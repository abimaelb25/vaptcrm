<?php

declare(strict_types=1);

namespace App\Jobs\WhatsApp;

use App\Models\WhatsApp\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppProviderResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job: send a single WhatsApp message via the provider adapter.
 *
 * Retry strategy:
 *  - Max 3 attempts with exponential backoff
 *  - On final failure, message status is set to 'failed'
 *  - Idempotent: skips messages already in non-pending state
 *
 * Queue: whatsapp (configure in config/queue.php or use 'default' fallback)
 */
class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries    = 3;
    public int $maxExceptions = 2;

    /** Backoff in seconds: 30s, 2min, 5min */
    public array $backoff = [30, 120, 300];

    public function __construct(private int $messageId) {}

    public function handle(WhatsAppProviderResolver $resolver): void
    {
        $message = WhatsAppMessage::find($this->messageId);

        if ($message === null) {
            Log::warning('SendWhatsAppMessageJob: message not found', ['id' => $this->messageId]);
            return;
        }

        // Idempotency — skip if already processed (e.g. duplicate job)
        if ($message->status !== WhatsAppMessage::STATUS_PENDING) {
            return;
        }

        $account  = $message->account;
        $provider = $resolver->resolve($account);

        try {
            $result = $this->sendViaProvider($provider, $account, $message);

            $message->update([
                'status'          => WhatsAppMessage::STATUS_SENT,
                'meta_message_id' => $result['message_id'] ?? null,
                'sent_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SendWhatsAppMessageJob: send failed', [
                'message_id' => $this->messageId,
                'attempt'    => $this->attempts(),
                'error'      => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $message->update([
                    'status'        => WhatsAppMessage::STATUS_FAILED,
                    'failed_at'     => now(),
                    'error_code'    => substr((string) $e->getCode(), 0, 20),
                    'error_message' => substr($e->getMessage(), 0, 500),
                ]);
                return;
            }

            throw $e; // Trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        $message = WhatsAppMessage::find($this->messageId);
        $message?->update([
            'status'        => WhatsAppMessage::STATUS_FAILED,
            'failed_at'     => now(),
            'error_message' => substr($exception->getMessage(), 0, 500),
        ]);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function sendViaProvider(
        \App\Contracts\WhatsApp\WhatsAppProviderInterface $provider,
        \App\Models\WhatsApp\WhatsAppAccount $account,
        WhatsAppMessage $message
    ): array {
        $conversation = $message->conversation;
        $toPhone      = $conversation->contact_phone;

        return match ($message->type) {
            WhatsAppMessage::TYPE_TEXT => $provider->sendTextMessage(
                $account->phone_number_id,
                $account->access_token,
                $toPhone,
                $message->body ?? ''
            ),

            WhatsAppMessage::TYPE_TEMPLATE => (function () use ($provider, $account, $toPhone, $message): array {
                $tpl = $message->template_data ?? [];
                return $provider->sendTemplateMessage(
                    $account->phone_number_id,
                    $account->access_token,
                    $toPhone,
                    $tpl['name']       ?? '',
                    $tpl['language']   ?? 'pt_BR',
                    $tpl['components'] ?? [],
                );
            })(),

            WhatsAppMessage::TYPE_IMAGE,
            WhatsAppMessage::TYPE_DOCUMENT,
            WhatsAppMessage::TYPE_AUDIO,
            WhatsAppMessage::TYPE_VIDEO => $provider->sendMediaMessage(
                $account->phone_number_id,
                $account->access_token,
                $toPhone,
                $message->type,
                $message->media_url ?? '',
                $message->body
            ),

            default => throw new \UnexpectedValueException(
                "SendWhatsAppMessageJob: unsupported message type '{$message->type}'"
            ),
        };
    }
}
