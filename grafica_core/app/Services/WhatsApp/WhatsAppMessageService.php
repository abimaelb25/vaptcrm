<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Loja;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppOptIn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Core message-sending service.
 *
 * All outbound sends go through this class so that:
 *  - opt-in is always verified
 *  - monthly quota is always enforced
 *  - messages are always logged
 *  - 24h window is respected for free-form text
 *
 * Actual dispatch is done via SendWhatsAppMessageJob (queue).
 */
class WhatsAppMessageService
{
    public function __construct(
        private WhatsAppAccountService  $accountService,
        private WhatsAppProviderResolver $resolver,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Send a text message (only within 24h service window).
     *
     * @throws \DomainException if window is closed, opt-in missing, or quota exceeded
     */
    public function sendText(
        WhatsAppAccount $account,
        WhatsAppConversation $conversation,
        string $text,
        ?int $sentByUserId = null
    ): WhatsAppMessage {
        $this->assertCanSendFreeForm($account, $conversation);

        return $this->recordAndDispatch(
            account: $account,
            conversation: $conversation,
            type: WhatsAppMessage::TYPE_TEXT,
            body: $text,
            sentBy: $sentByUserId,
        );
    }

    /**
     * Send an approved template message (works outside service window).
     */
    public function sendTemplate(
        WhatsAppAccount $account,
        WhatsAppConversation $conversation,
        string $templateName,
        string $languageCode,
        array $components = [],
        ?int $sentByUserId = null,
        bool $isAutomated = false
    ): WhatsAppMessage {
        $this->assertHasOptIn($account->loja_id, $conversation->contact_phone);
        $this->assertQuotaAvailable($account->loja_id);

        return $this->recordAndDispatch(
            account: $account,
            conversation: $conversation,
            type: WhatsAppMessage::TYPE_TEMPLATE,
            body: null,
            sentBy: $sentByUserId,
            isAutomated: $isAutomated,
            templateData: [
                'name'       => $templateName,
                'language'   => $languageCode,
                'components' => $components,
            ],
        );
    }

    /**
     * Send a media message (image, document, etc.).
     */
    public function sendMedia(
        WhatsAppAccount $account,
        WhatsAppConversation $conversation,
        string $type,
        string $mediaUrl,
        ?string $caption = null,
        ?int $sentByUserId = null
    ): WhatsAppMessage {
        $this->assertCanSendFreeForm($account, $conversation);

        return $this->recordAndDispatch(
            account: $account,
            conversation: $conversation,
            type: $type,
            body: $caption,
            sentBy: $sentByUserId,
            mediaUrl: $mediaUrl,
        );
    }

    /**
     * Mark an inbound message as read (sends receipt to Meta).
     */
    public function markRead(WhatsAppMessage $message): void
    {
        if ($message->direction !== WhatsAppMessage::DIRECTION_INBOUND) {
            return;
        }
        if ($message->meta_message_id === null) {
            return;
        }

        $account  = $message->account;
        $provider = $this->resolver->resolve($account);

        try {
            $provider->markAsRead(
                $account->phone_number_id,
                $account->access_token,
                $message->meta_message_id
            );
        } catch (\Throwable $e) {
            Log::warning('WhatsApp: markAsRead failed', [
                'message_id' => $message->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Persist the message record (status=pending) and dispatch the send job.
     */
    private function recordAndDispatch(
        WhatsAppAccount $account,
        WhatsAppConversation $conversation,
        string $type,
        ?string $body,
        ?int $sentBy = null,
        bool $isAutomated = false,
        ?string $mediaUrl = null,
        ?array $templateData = null,
    ): WhatsAppMessage {
        return DB::transaction(function () use (
            $account, $conversation, $type, $body,
            $sentBy, $isAutomated, $mediaUrl, $templateData
        ) {
            $message = WhatsAppMessage::create([
                'loja_id'              => $account->loja_id,
                'whatsapp_account_id'  => $account->id,
                'conversation_id'      => $conversation->id,
                'direction'            => WhatsAppMessage::DIRECTION_OUTBOUND,
                'type'                 => $type,
                'status'               => WhatsAppMessage::STATUS_PENDING,
                'body'                 => $body,
                'media_url'            => $mediaUrl,
                'template_data'        => $templateData,
                'sent_by'              => $sentBy,
                'is_automated'         => $isAutomated,
            ]);

            // Update conversation last activity
            $conversation->update([
                'last_message_at' => now(),
                'last_message_id' => $message->id,
            ]);

            // Dispatch to queue
            \App\Jobs\WhatsApp\SendWhatsAppMessageJob::dispatch($message->id);

            return $message;
        });
    }

    private function assertCanSendFreeForm(
        WhatsAppAccount $account,
        WhatsAppConversation $conversation
    ): void {
        if (! $conversation->isWithinServiceWindow()) {
            throw new \DomainException(
                'A janela de 24h de atendimento está encerrada. ' .
                'Use um template aprovado para iniciar nova conversa.'
            );
        }
        $this->assertHasOptIn($account->loja_id, $conversation->contact_phone);
        $this->assertQuotaAvailable($account->loja_id);
    }

    private function assertHasOptIn(int $lojaId, string $phone): void
    {
        $optin = WhatsAppOptIn::where('loja_id', $lojaId)
            ->where('phone', $phone)
            ->first();

        if ($optin === null || ! $optin->hasOptedIn()) {
            throw new \DomainException(
                "O contato {$phone} não deu opt-in para receber mensagens WhatsApp."
            );
        }
    }

    private function assertQuotaAvailable(int $lojaId): void
    {
        $loja = \App\Models\Loja::findOrFail($lojaId);
        $this->accountService->assertMonthlyMessageLimitNotExceeded($loja);
    }
}
