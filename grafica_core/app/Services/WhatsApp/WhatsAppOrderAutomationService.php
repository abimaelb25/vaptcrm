<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Pedido;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppOptIn;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Order Automation Service
 *
 * Sends template messages at each order status transition.
 * Each method is idempotent — if the account/template/optin is missing, it
 * logs silently and returns without throwing (non-critical automation).
 *
 * Template keys (WhatsAppTemplate::KEY_*):
 *   order_created    → pedido criado / orçamento enviado
 *   order_in_production
 *   order_ready
 *   order_delivered
 *
 * AI layer preparation:
 *   All automated messages are stored with is_automated=true.
 *   A future AI service can query these to build conversation context.
 */
class WhatsAppOrderAutomationService
{
    public function __construct(
        private WhatsAppAccountService  $accountService,
        private WhatsAppMessageService  $messageService,
        private WhatsAppTemplateService $templateService,
        private WhatsAppConversationService $conversationService,
        private WhatsAppSettingsService $settingsService,
    ) {}

    // -------------------------------------------------------------------------
    // Public automation triggers
    // -------------------------------------------------------------------------

    public function onOrderCreated(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_ORDER_CREATED);
    }

    public function onQuoteSent(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_ORDER_QUOTE_SENT);
    }

    public function onOrderInProduction(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_ORDER_PRODUCTION);
    }

    public function onOrderReady(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_ORDER_READY);
    }

    public function onOrderDelivered(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_ORDER_DELIVERED);
    }

    public function onPaymentConfirmed(Pedido $pedido): void
    {
        $this->sendAutomation($pedido, WhatsAppTemplate::KEY_PAYMENT_CONFIRMED);
    }

    // -------------------------------------------------------------------------
    // Core
    // -------------------------------------------------------------------------

    private function sendAutomation(Pedido $pedido, string $templateKey): void
    {
        // Resolve the loja
        $lojaId = $pedido->loja_id;

        // Resolve the primary WhatsApp account
        $loja    = $pedido->loja ?? \App\Models\Loja::find($lojaId);
        if ($loja === null) {
            return;
        }

        $settings = $this->settingsService->findForLoja($lojaId);
        if (!$this->settingsService->shouldAutoSend($loja, $settings)) {
            Log::debug('WhatsApp automation: auto-send disabled by send mode', [
                'pedido_id' => $pedido->id,
                'template_key' => $templateKey,
            ]);
            return;
        }

        $account = $this->accountService->getPrimaryAccount($loja);
        if ($account === null) {
            // No active WhatsApp account — silently skip
            return;
        }

        // Resolve customer phone
        $phone = $this->resolvePhone($pedido);
        if ($phone === null) {
            Log::debug('WhatsApp automation: no phone for pedido', ['pedido_id' => $pedido->id]);
            return;
        }

        // Check opt-in
        $optin = WhatsAppOptIn::where('loja_id', $lojaId)
            ->where('phone', $phone)
            ->first();

        if ($optin === null || ! $optin->hasOptedIn()) {
            Log::debug('WhatsApp automation: no optin', [
                'pedido_id' => $pedido->id,
                'phone'     => $phone,
            ]);
            return;
        }

        if ($settings !== null) {
            if (! $settings->isAutomationEnabled($templateKey)) {
                return;
            }

            $template = $this->settingsService->resolveTemplateForEvent($settings, $templateKey);
            if ($template === null) {
                Log::debug('WhatsApp automation: mapped template not found', [
                    'pedido_id' => $pedido->id,
                    'template_key' => $templateKey,
                ]);
                return;
            }

            $components = $this->settingsService->buildComponentsForEvent($settings, $templateKey, $pedido, $loja);
        } else {
            $template = $this->templateService->resolveSystemTemplate($account, $templateKey);
            if ($template === null) {
                Log::debug('WhatsApp automation: template not found or not approved', [
                    'pedido_id'    => $pedido->id,
                    'template_key' => $templateKey,
                ]);
                return;
            }

            $components = $this->buildComponents($pedido, $templateKey);
        }

        // Get or create conversation
        $conversation = $this->conversationService->findOrCreateFromInbound(
            $account, $phone
        );

        try {
            $this->messageService->sendTemplate(
                account: $account,
                conversation: $conversation,
                templateName: $template->name,
                languageCode: $template->language,
                components: $components,
                sentByUserId: null,
                isAutomated: true,
            );

            Log::info('WhatsApp automation: message sent', [
                'pedido_id'    => $pedido->id,
                'template_key' => $templateKey,
            ]);
        } catch (\DomainException $e) {
            // Quota exceeded or other domain rule — log and skip
            Log::warning('WhatsApp automation: domain exception', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp automation: unexpected error', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolvePhone(Pedido $pedido): ?string
    {
        $cliente = $pedido->cliente ?? null;
        if ($cliente === null) {
            return null;
        }

        $phone = $cliente->whatsapp ?? $cliente->telefone ?? null;
        if ($phone === null) {
            return null;
        }

        // Normalise to E.164 (basic BR normalisation)
        return $this->normalisePhone($phone);
    }

    private function normalisePhone(string $phone): string
    {
        // Strip non-digits
        $digits = preg_replace('/\D/', '', $phone);

        // Already has country code
        if (strlen($digits) >= 12) {
            return '+' . $digits;
        }

        // Assume BR +55
        if (strlen($digits) === 11 || strlen($digits) === 10) {
            return '+55' . $digits;
        }

        return '+' . $digits;
    }

    /**
     * Build template body components with dynamic variables per key.
     *
     * Template body variables are positional: {{1}}, {{2}}, etc.
     * Adjust order/values to match your approved Meta templates.
     */
    private function buildComponents(Pedido $pedido, string $templateKey): array
    {
        $clienteNome = $pedido->cliente?->nome ?? 'Cliente';
        $numeroPedido = $pedido->numero ?? $pedido->id;
        $total        = 'R$ ' . number_format((float) $pedido->total, 2, ',', '.');

        $bodyParams = match ($templateKey) {
            WhatsAppTemplate::KEY_ORDER_CREATED => [
                ['type' => 'text', 'text' => $clienteNome],
                ['type' => 'text', 'text' => (string) $numeroPedido],
                ['type' => 'text', 'text' => $total],
            ],
            WhatsAppTemplate::KEY_ORDER_QUOTE_SENT => [
                ['type' => 'text', 'text' => $clienteNome],
                ['type' => 'text', 'text' => (string) $numeroPedido],
                ['type' => 'text', 'text' => $total],
            ],
            WhatsAppTemplate::KEY_ORDER_PRODUCTION => [
                ['type' => 'text', 'text' => $clienteNome],
                ['type' => 'text', 'text' => (string) $numeroPedido],
            ],
            WhatsAppTemplate::KEY_ORDER_READY => [
                ['type' => 'text', 'text' => $clienteNome],
                ['type' => 'text', 'text' => (string) $numeroPedido],
            ],
            WhatsAppTemplate::KEY_ORDER_DELIVERED => [
                ['type' => 'text', 'text' => $clienteNome],
                ['type' => 'text', 'text' => (string) $numeroPedido],
            ],
            default => [],
        };

        if (empty($bodyParams)) {
            return [];
        }

        return [
            [
                'type'       => 'body',
                'parameters' => $bodyParams,
            ],
        ];
    }
}
