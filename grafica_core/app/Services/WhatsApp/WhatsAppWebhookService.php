<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Processes incoming WhatsApp webhook events.
 *
 * Flow:
 *   1. Raw payload is stored in whatsapp_webhook_events (always, even on error)
 *   2. Provider parses the payload into a normalised event
 *   3. Event is routed: message | status
 *   4. Event record is marked processed/failed
 */
class WhatsAppWebhookService
{
    public function __construct(
        private WhatsAppProviderResolver    $resolver,
        private WhatsAppConversationService $conversationService,
    ) {}

    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------

    /**
     * Handle a raw inbound webhook payload.
     * Always persists the raw event for audit regardless of processing outcome.
     */
    public function handle(array $payload, string $provider = 'meta_cloud'): void
    {
        $event = WhatsAppWebhookEvent::create([
            'provider'          => $provider,
            'payload'           => $payload,
            'processing_status' => WhatsAppWebhookEvent::STATUS_PENDING,
        ]);

        try {
            $this->process($event, $payload, $provider);
        } catch (\Throwable $e) {
            $event->update([
                'processing_status' => WhatsAppWebhookEvent::STATUS_FAILED,
                'processing_error'  => substr($e->getMessage(), 0, 500),
                'retry_count'       => $event->retry_count + 1,
            ]);

            Log::error('WhatsApp: webhook processing failed', [
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Processing
    // -------------------------------------------------------------------------

    private function process(
        WhatsAppWebhookEvent $event,
        array $payload,
        string $provider
    ): void {
        // Resolve account from phone_number_id in payload
        $phoneNumberId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']
            ?? null;

        if ($phoneNumberId === null) {
            $event->update(['processing_status' => WhatsAppWebhookEvent::STATUS_SKIPPED]);
            return;
        }

        /** @var WhatsAppAccount|null $account */
        $account = WhatsAppAccount::where('phone_number_id', $phoneNumberId)
            ->where('status', WhatsAppAccount::STATUS_ACTIVE)
            ->first();

        if ($account === null) {
            Log::warning('WhatsApp: webhook for unknown phone_number_id', [
                'phone_number_id' => $phoneNumberId,
            ]);
            $event->update(['processing_status' => WhatsAppWebhookEvent::STATUS_SKIPPED]);
            return;
        }

        // Update event with loja context
        $event->update(['loja_id' => $account->loja_id]);

        // Parse via provider adapter
        $providerAdapter = $this->resolver->resolve($account);
        $parsed          = $providerAdapter->parseWebhookPayload($payload);

        if ($parsed === null) {
            $event->update(['processing_status' => WhatsAppWebhookEvent::STATUS_SKIPPED]);
            return;
        }

        $event->update([
            'event_type'      => $parsed['event_type'],
            'meta_message_id' => $parsed['message_id'] ?? null,
        ]);

        DB::transaction(function () use ($account, $parsed, $event) {
            match ($parsed['event_type']) {
                'message' => $this->handleMessageEvent($account, $parsed),
                'status'  => $this->handleStatusEvent($parsed),
                default   => null,
            };

            $event->update([
                'processing_status' => WhatsAppWebhookEvent::STATUS_PROCESSED,
                'processed_at'      => now(),
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Event handlers
    // -------------------------------------------------------------------------

    private function handleMessageEvent(WhatsAppAccount $account, array $parsed): void
    {
        $fromPhone   = $parsed['from_phone'];
        $contactName = $parsed['contact_name'] ?? null;
        $messageId   = $parsed['message_id'];

        // Idempotency — skip duplicate wamid
        if (WhatsAppMessage::where('meta_message_id', $messageId)->exists()) {
            return;
        }

        // Get or create conversation (also refreshes 24h window)
        $conversation = $this->conversationService->findOrCreateFromInbound(
            $account, $fromPhone, $contactName
        );

        // Persist inbound message
        WhatsAppMessage::create([
            'loja_id'              => $account->loja_id,
            'whatsapp_account_id'  => $account->id,
            'conversation_id'      => $conversation->id,
            'meta_message_id'      => $messageId,
            'direction'            => WhatsAppMessage::DIRECTION_INBOUND,
            'type'                 => $parsed['type'] ?? WhatsAppMessage::TYPE_TEXT,
            'status'               => WhatsAppMessage::STATUS_RECEIVED,
            'body'                 => $parsed['body'],
            'is_automated'         => false,
        ]);

        // Mark conversation as unread
        $conversation->update([
            'last_message_at' => now(),
            'is_unread'       => true,
        ]);
    }

    private function handleStatusEvent(array $parsed): void
    {
        $metaMessageId = $parsed['message_id'];
        $newStatus     = $parsed['status'] ?? null;

        if ($metaMessageId === null || $newStatus === null) {
            return;
        }

        $message = WhatsAppMessage::where('meta_message_id', $metaMessageId)->first();
        if ($message === null) {
            return;
        }

        $statusMap = [
            'sent'      => WhatsAppMessage::STATUS_SENT,
            'delivered' => WhatsAppMessage::STATUS_DELIVERED,
            'read'      => WhatsAppMessage::STATUS_READ,
            'failed'    => WhatsAppMessage::STATUS_FAILED,
        ];

        $mappedStatus = $statusMap[$newStatus] ?? null;
        if ($mappedStatus === null) {
            return;
        }

        $update = ['status' => $mappedStatus];

        match ($mappedStatus) {
            WhatsAppMessage::STATUS_SENT      => $update['sent_at']      = now(),
            WhatsAppMessage::STATUS_DELIVERED => $update['delivered_at'] = now(),
            WhatsAppMessage::STATUS_READ      => $update['read_at']      = now(),
            WhatsAppMessage::STATUS_FAILED    => $update['failed_at']    = now(),
            default                           => null,
        };

        $message->update($update);
    }
}
