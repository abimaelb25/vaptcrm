<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Loja;
use App\Models\Pedido;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppOptIn;
use App\Models\WhatsApp\WhatsAppStoreSetting;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Support\PublicUrlHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WhatsAppSettingsService
{
    public function __construct(
        private WhatsAppConversationService $conversationService,
        private WhatsAppMessageService $messageService,
    ) {}

    public function getOrCreate(Loja $loja): WhatsAppStoreSetting
    {
        return WhatsAppStoreSetting::firstOrCreate(
            ['loja_id' => $loja->id],
            [
                'catalog_link' => PublicUrlHelper::catalogo($loja),
                'send_mode' => WhatsAppStoreSetting::SEND_MODE_API,
                'automations' => [],
                'event_mappings' => [],
            ]
        );
    }

    public function findForLoja(int $lojaId): ?WhatsAppStoreSetting
    {
        return WhatsAppStoreSetting::where('loja_id', $lojaId)->first();
    }

    public function update(Loja $loja, array $payload): WhatsAppStoreSetting
    {
        $settings = $this->getOrCreate($loja);

        $automations = $settings->automations ?? [];
        if (array_key_exists('automations', $payload)) {
            $automations = [];
            foreach (array_keys($this->eventOptions()) as $eventKey) {
                $automations[$eventKey] = (bool) Arr::get($payload, "automations.{$eventKey}", false);
            }
        }

        $eventMappings = $settings->event_mappings ?? [];
        if (array_key_exists('mappings', $payload)) {
            $eventMappings = [];
            foreach (array_keys($this->eventOptions()) as $eventKey) {
                $rawVariables = (array) Arr::get($payload, "mappings.{$eventKey}.variables", []);
                $variables = array_values(array_filter($rawVariables, static fn (?string $value): bool => !empty($value)));

                $eventMappings[$eventKey] = [
                    'template_id' => Arr::get($payload, "mappings.{$eventKey}.template_id") ?: null,
                    'variables' => $variables,
                ];
            }
        }

        $settings->update([
            'default_account_id'             => $payload['default_account_id'] ?? $settings->default_account_id,
            'catalog_link'                   => $payload['catalog_link'] ?? $settings->catalog_link ?? PublicUrlHelper::catalogo($loja),
            'send_mode'                      => $payload['send_mode'] ?? $settings->send_mode ?? WhatsAppStoreSetting::SEND_MODE_API,
            'automations'                    => $automations,
            'event_mappings'                 => $eventMappings,
            'last_test_phone'                => $payload['last_test_phone'] ?? $settings->last_test_phone,
            'ai_suggestions_enabled'         => isset($payload['ai_suggestions_enabled']) ? (bool) $payload['ai_suggestions_enabled'] : (bool) ($settings->ai_suggestions_enabled ?? false),
            'ai_auto_classification_enabled' => isset($payload['ai_auto_classification_enabled']) ? (bool) $payload['ai_auto_classification_enabled'] : (bool) ($settings->ai_auto_classification_enabled ?? false),
            'ai_handoff_required'            => isset($payload['ai_handoff_required']) ? (bool) $payload['ai_handoff_required'] : (bool) ($settings->ai_handoff_required ?? true),
            'quote_recovery_enabled'         => isset($payload['quote_recovery_enabled']) ? (bool) $payload['quote_recovery_enabled'] : (bool) ($settings->quote_recovery_enabled ?? false),
            'click_to_whatsapp_enabled'      => isset($payload['click_to_whatsapp_enabled']) ? (bool) $payload['click_to_whatsapp_enabled'] : (bool) ($settings->click_to_whatsapp_enabled ?? false),
        ]);

        return $settings->fresh();
    }

    public function resolveTemplateForEvent(WhatsAppStoreSetting $settings, string $eventKey): ?WhatsAppTemplate
    {
        $mapping = $settings->mappingFor($eventKey);
        $templateId = $mapping['template_id'] ?? null;

        if (empty($templateId)) {
            return null;
        }

        return WhatsAppTemplate::where('loja_id', $settings->loja_id)
            ->where('id', $templateId)
            ->where('status', WhatsAppTemplate::STATUS_APPROVED)
            ->first();
    }

    public function buildComponentsForEvent(
        WhatsAppStoreSetting $settings,
        string $eventKey,
        ?Pedido $pedido,
        Loja $loja
    ): array {
        $mapping = $settings->mappingFor($eventKey);
        $variables = array_values(array_filter((array) ($mapping['variables'] ?? [])));

        if ($variables === []) {
            return [];
        }

        $context = $this->contextFor($loja, $pedido);

        return [[
            'type' => 'body',
            'parameters' => array_map(
                static fn (string $key): array => ['type' => 'text', 'text' => (string) ($context[$key] ?? '-')],
                $variables
            ),
        ]];
    }

    public function buildTestComponents(Loja $loja, ?string $eventKey = null): array
    {
        $context = $this->contextFor($loja, null);
        $variables = $eventKey !== null
            ? array_values(array_filter((array) ($this->getOrCreate($loja)->mappingFor($eventKey)['variables'] ?? [])))
            : ['cliente_nome', 'pedido_numero'];

        if ($variables === []) {
            $variables = ['cliente_nome', 'pedido_numero'];
        }

        return [[
            'type' => 'body',
            'parameters' => array_map(
                static fn (string $key): array => ['type' => 'text', 'text' => (string) ($context[$key] ?? '-')],
                $variables
            ),
        ]];
    }

    public function sendTemplateTest(
        Loja $loja,
        WhatsAppAccount $account,
        WhatsAppTemplate $template,
        string $phone,
        ?string $eventKey = null,
        ?Pedido $pedido = null,
    ): void {
        WhatsAppOptIn::updateOrCreate(
            ['loja_id' => $loja->id, 'phone' => $phone],
            [
                'status' => WhatsAppOptIn::STATUS_OPTED_IN,
                'source' => 'admin_test_send',
                'opted_in_at' => now(),
            ]
        );

        $conversation = $this->conversationService->findOrCreateFromInbound($account, $phone, 'Teste WhatsApp');

        $components = $this->buildTestComponents($loja, $eventKey);
        if ($pedido !== null && $eventKey !== null) {
            $settings = $this->getOrCreate($loja);
            $realComponents = $this->buildComponentsForEvent($settings, $eventKey, $pedido, $loja);
            if ($realComponents !== []) {
                $components = $realComponents;
            }
        }

        $this->messageService->sendTemplate(
            account: $account,
            conversation: $conversation,
            templateName: $template->name,
            languageCode: $template->language,
            components: $components,
            sentByUserId: auth()->id(),
            isAutomated: false,
        );
    }

    public function eventOptions(): array
    {
        return [
            WhatsAppTemplate::KEY_ORDER_CREATED => 'Pedido criado',
            WhatsAppTemplate::KEY_ORDER_QUOTE_SENT => 'Orçamento enviado',
            WhatsAppTemplate::KEY_PAYMENT_CONFIRMED => 'Pagamento confirmado',
            WhatsAppTemplate::KEY_ORDER_PRODUCTION => 'Pedido em produção',
            WhatsAppTemplate::KEY_ORDER_READY => 'Pedido pronto',
            WhatsAppTemplate::KEY_ORDER_DELIVERED => 'Pedido entregue',
        ];
    }

    public function variableOptions(): array
    {
        return [
            'cliente_nome' => 'Nome do cliente',
            'pedido_numero' => 'Número do pedido',
            'orcamento_valor' => 'Valor do orçamento',
            'catalogo_link' => 'Link do catálogo',
            'status_pedido' => 'Status do pedido',
        ];
    }

    public function sendModeOptions(): array
    {
        return [
            WhatsAppStoreSetting::SEND_MODE_MANUAL => 'Manual (link WhatsApp)',
            WhatsAppStoreSetting::SEND_MODE_API => 'Automático (API oficial)',
        ];
    }

    public function hasActiveApiIntegration(int $lojaId): bool
    {
        return WhatsAppAccount::where('loja_id', $lojaId)
            ->where('status', WhatsAppAccount::STATUS_ACTIVE)
            ->exists();
    }

    public function resolveEffectiveSendMode(Loja $loja, ?WhatsAppStoreSetting $settings = null): string
    {
        $settings ??= $this->getOrCreate($loja);

        if (($settings->send_mode ?? WhatsAppStoreSetting::SEND_MODE_API) === WhatsAppStoreSetting::SEND_MODE_MANUAL) {
            return WhatsAppStoreSetting::SEND_MODE_MANUAL;
        }

        if (!$this->hasActiveApiIntegration($loja->id)) {
            return WhatsAppStoreSetting::SEND_MODE_MANUAL;
        }

        return WhatsAppStoreSetting::SEND_MODE_API;
    }

    public function shouldAutoSend(Loja $loja, ?WhatsAppStoreSetting $settings = null): bool
    {
        return $this->resolveEffectiveSendMode($loja, $settings) === WhatsAppStoreSetting::SEND_MODE_API;
    }

    public function eventKeyForPedidoStatus(string $status): string
    {
        return match ($status) {
            Pedido::STATUS_AGUARDANDO, Pedido::STATUS_AGUARDANDO_PAGAMENTO => WhatsAppTemplate::KEY_ORDER_QUOTE_SENT,
            Pedido::STATUS_EM_PRODUCAO => WhatsAppTemplate::KEY_ORDER_PRODUCTION,
            Pedido::STATUS_PRONTO => WhatsAppTemplate::KEY_ORDER_READY,
            Pedido::STATUS_ENTREGUE => WhatsAppTemplate::KEY_ORDER_DELIVERED,
            default => WhatsAppTemplate::KEY_ORDER_CREATED,
        };
    }

    public function buildManualMessageForEvent(Loja $loja, Pedido $pedido, string $eventKey): string
    {
        $settings = $this->getOrCreate($loja);
        $context = $this->contextFor($loja, $pedido);

        $fallbackTemplates = [
            WhatsAppTemplate::KEY_ORDER_CREATED => 'Ola {{cliente_nome}}, seu pedido {{pedido_numero}} foi registrado. Valor: {{orcamento_valor}}.',
            WhatsAppTemplate::KEY_ORDER_QUOTE_SENT => 'Ola {{cliente_nome}}, seu orcamento do pedido {{pedido_numero}} esta pronto. Valor: {{orcamento_valor}}.',
            WhatsAppTemplate::KEY_PAYMENT_CONFIRMED => 'Pagamento confirmado para o pedido {{pedido_numero}}. Seguimos com a producao.',
            WhatsAppTemplate::KEY_ORDER_PRODUCTION => 'Ola {{cliente_nome}}, seu pedido {{pedido_numero}} esta {{status_pedido}}.',
            WhatsAppTemplate::KEY_ORDER_READY => 'Boa noticia: seu pedido {{pedido_numero}} esta pronto para retirada.',
            WhatsAppTemplate::KEY_ORDER_DELIVERED => 'Pedido {{pedido_numero}} entregue. Obrigado por comprar com a gente!',
        ];

        $template = $fallbackTemplates[$eventKey] ?? $fallbackTemplates[WhatsAppTemplate::KEY_ORDER_CREATED];
        $configuredVariables = (array) ($settings->mappingFor($eventKey)['variables'] ?? []);
        $variables = array_values(array_filter($configuredVariables));

        if ($variables !== []) {
            foreach (array_keys($context) as $contextKey) {
                if (!in_array($contextKey, $variables, true)) {
                    $template = str_replace('{{' . $contextKey . '}}', '', $template);
                }
            }
        }

        foreach ($context as $contextKey => $contextValue) {
            $template = str_replace('{{' . $contextKey . '}}', (string) $contextValue, $template);
        }

        return trim((string) preg_replace('/\s+/', ' ', $template));
    }

    public function buildWaMeLinkForEvent(Loja $loja, Pedido $pedido, string $eventKey): ?string
    {
        $phone = $pedido->cliente?->whatsapp ?? $pedido->cliente?->telefone;
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (empty($digits)) {
            return null;
        }

        if (Str::startsWith($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = '55' . $digits;
        }

        $message = $this->buildManualMessageForEvent($loja, $pedido, $eventKey);

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }

    private function contextFor(Loja $loja, ?Pedido $pedido): array
    {
        return [
            'cliente_nome' => $pedido?->cliente?->nome ?? 'Cliente Teste',
            'pedido_numero' => (string) ($pedido?->numero_exibicao ?? $pedido?->numero ?? 'PED-TESTE-001'),
            'orcamento_valor' => 'R$ ' . number_format((float) ($pedido?->total ?? 149.90), 2, ',', '.'),
            'catalogo_link' => $this->getOrCreate($loja)->catalog_link ?: PublicUrlHelper::catalogo($loja),
            'status_pedido' => (string) ($pedido?->status ?? 'aguardando_aprovacao'),
        ];
    }
}