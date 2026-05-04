<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppStoreSetting;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\WhatsApp\WhatsAppWebhookEvent;
use App\Models\WhatsApp\WhatsAppConversationNote;
use App\Services\WhatsApp\WhatsAppAccountService;
use App\Services\WhatsApp\WhatsAppConversationService;
use App\Services\WhatsApp\WhatsAppDashboardService;
use App\Services\WhatsApp\WhatsAppMessageService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WhatsAppOperationsController extends Controller
{
    public function __construct(
        private WhatsAppAccountService $accountService,
        private WhatsAppTemplateService $templateService,
        private WhatsAppSettingsService $settingsService,
        private WhatsAppConversationService $conversationService,
        private WhatsAppMessageService $messageService,
        private WhatsAppDashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $loja = Auth::user()->loja;
        $settings = $this->settingsService->getOrCreate($loja);
        $accounts = WhatsAppAccount::forLoja($loja->id)
            ->with(['templates' => fn ($query) => $query->orderBy('name')])
            ->orderByDesc('is_primary')
            ->orderBy('display_name')
            ->get();

        $messagesUsed = $this->accountService->countMonthlyMessagesSent($loja->id);
        $messageLimit = $this->accountService->getMonthlyMessageLimit($loja);
        $openConversations = WhatsAppConversation::where('loja_id', $loja->id)
            ->whereIn('status', [WhatsAppConversation::STATUS_OPEN, WhatsAppConversation::STATUS_WAITING])
            ->count();

        $automationFlags = (array) ($settings->automations ?? []);
        $eventMappings = (array) ($settings->event_mappings ?? []);
        $automationsConfigured = false;

        foreach ($this->settingsService->eventOptions() as $eventKey => $eventLabel) {
            if (!empty($automationFlags[$eventKey]) && !empty($eventMappings[$eventKey]['template_id'])) {
                $automationsConfigured = true;
                break;
            }
        }

        $sampleContext = [
            'cliente_nome' => 'João',
            'pedido_numero' => '#123',
            'orcamento_valor' => 'R$ 245,90',
            'catalogo_link' => $settings->catalog_link ?: '#',
            'status_pedido' => 'em produção',
        ];

        $eventPreviews = [];
        foreach ($this->settingsService->eventOptions() as $eventKey => $eventLabel) {
            $variables = array_values(array_filter((array) ($eventMappings[$eventKey]['variables'] ?? [])));
            $eventPreviews[$eventKey] = $this->renderPreviewMessage($eventKey, $variables, $sampleContext);
        }
        $effectiveSendMode = $this->settingsService->resolveEffectiveSendMode($loja, $settings);

        $progressSteps = [
            [
                'title' => 'Conectar WhatsApp',
                'description' => 'Leva cerca de 2 minutos.',
                'done' => $accounts->isNotEmpty(),
                'cta' => 'Conectar agora',
                'anchor' => '#connection-form',
            ],
            [
                'title' => 'Escolher automações',
                'description' => 'Ative as mensagens que deseja enviar automaticamente.',
                'done' => $effectiveSendMode === WhatsAppStoreSetting::SEND_MODE_MANUAL || $automationsConfigured,
                'cta' => 'Configurar automações',
                'anchor' => '#automations',
            ],
            [
                'title' => 'Testar envio',
                'description' => 'Envie uma mensagem teste para validar tudo.',
                'done' => !empty($settings->last_test_phone),
                'cta' => 'Enviar teste',
                'anchor' => '#test-send',
            ],
            [
                'title' => 'Abrir inbox',
                'description' => 'Comece a atender conversas reais.',
                'done' => $openConversations > 0,
                'cta' => 'Abrir conversas',
                'anchor' => route('admin.whatsapp.page.inbox'),
            ],
        ];

        $recentOrders = Pedido::where('loja_id', $loja->id)
            ->with('cliente')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('painel.whatsapp.index', [
            'loja' => $loja,
            'settings' => $settings,
            'accounts' => $accounts,
            'sendModeOptions' => $this->settingsService->sendModeOptions(),
            'effectiveSendMode' => $effectiveSendMode,
            'eventOptions' => $this->settingsService->eventOptions(),
            'variableOptions' => $this->settingsService->variableOptions(),
            'messageLimit' => $messageLimit,
            'messagesUsed' => $messagesUsed,
            'openConversations' => $openConversations,
            'monthlyRemaining' => $messageLimit === null ? null : max(0, $messageLimit - $messagesUsed),
            'usagePercent' => $messageLimit === null || $messageLimit === 0 ? 0 : (int) min(100, round(($messagesUsed / $messageLimit) * 100)),
            'isNearLimit' => $messageLimit !== null && $messageLimit > 0 && (($messagesUsed / $messageLimit) >= 0.8),
            'progressSteps' => $progressSteps,
            'eventPreviews' => $eventPreviews,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $loja = Auth::user()->loja;

        $validated = $request->validate([
            'waba_id' => ['required', 'string', 'max:80'],
            'phone_number_id' => ['required', 'string', 'max:80'],
            'phone_number' => ['required', 'string', 'max:30'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'access_token' => ['required', 'string', 'min:10', 'max:500'],
            'business_id' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $this->accountService->onboard($loja, $validated);
        } catch (\DomainException $e) {
            return back()->withErrors(['whatsapp_connection' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.whatsapp.index')->with('sucesso', 'Conta WhatsApp conectada com sucesso.');
    }

    public function syncTemplates(WhatsAppAccount $account): RedirectResponse
    {
        $this->authorizeAccount($account);
        $count = $this->templateService->syncFromMeta($account);

        return back()->with('sucesso', "{$count} template(s) sincronizado(s).");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $loja = Auth::user()->loja;

        $validated = $request->validate([
            'default_account_id' => ['nullable', 'integer', 'exists:whatsapp_accounts,id'],
            'catalog_link' => ['nullable', 'url', 'max:500'],
            'send_mode' => ['nullable', 'in:' . WhatsAppStoreSetting::SEND_MODE_MANUAL . ',' . WhatsAppStoreSetting::SEND_MODE_API],
            'automations' => ['nullable', 'array'],
            'mappings' => ['nullable', 'array'],
        ]);

        $payload = $request->all();
        $warning = null;

        if (!empty($validated['default_account_id'])) {
            $account = WhatsAppAccount::findOrFail((int) $validated['default_account_id']);
            $this->authorizeAccount($account);
        }

        if (($validated['send_mode'] ?? null) === WhatsAppStoreSetting::SEND_MODE_API
            && !$this->settingsService->hasActiveApiIntegration((int) $loja->id)) {
            $payload['send_mode'] = WhatsAppStoreSetting::SEND_MODE_MANUAL;
            $warning = 'Sem conta API ativa, o sistema foi mantido no modo manual por link.';
        }

        $this->settingsService->update($loja, $payload);

        $redirect = redirect()->route('admin.whatsapp.index')->with('sucesso', 'Preferências do WhatsApp atualizadas.');
        if ($warning !== null) {
            $redirect->with('erro', $warning);
        }

        return $redirect;
    }

    public function openManualLink(Request $request, Pedido $pedido): RedirectResponse
    {
        if ($pedido->loja_id !== Auth::user()->loja_id) {
            abort(403, 'Acesso negado.');
        }

        $eventKey = (string) $request->query('event_key', $this->settingsService->eventKeyForPedidoStatus((string) $pedido->status));
        if (!array_key_exists($eventKey, $this->settingsService->eventOptions())) {
            return back()->withErrors(['whatsapp_manual' => 'Evento de mensagem inválido.']);
        }

        $loja = Auth::user()->loja;
        $pedido->loadMissing('cliente');

        $waLink = $this->settingsService->buildWaMeLinkForEvent($loja, $pedido, $eventKey);
        if ($waLink === null) {
            return back()->withErrors(['whatsapp_manual' => 'Cliente sem número de WhatsApp válido para envio manual.']);
        }

        return redirect()->away($waLink);
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $loja = Auth::user()->loja;

        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:whatsapp_accounts,id'],
            'template_id' => ['required', 'integer', 'exists:whatsapp_templates,id'],
            'phone' => ['required', 'string', 'max:30'],
            'event_key' => ['nullable', 'string'],
            'use_real_data' => ['nullable', 'boolean'],
            'order_id' => ['nullable', 'integer', 'exists:pedidos,id'],
            'confirm_optin' => ['required', 'accepted'],
        ]);

        if (!preg_match('/^\+?[0-9]{10,15}$/', preg_replace('/\s+/', '', $validated['phone']))) {
            return back()->withErrors(['whatsapp_test' => 'Número inválido. Use o formato com DDI e DDD, por exemplo: +5511999999999.'])->withInput();
        }

        $account = WhatsAppAccount::findOrFail((int) $validated['account_id']);
        $template = WhatsAppTemplate::findOrFail((int) $validated['template_id']);

        $this->authorizeAccount($account);
        if ($template->loja_id !== $loja->id || $template->whatsapp_account_id !== $account->id) {
            abort(403, 'Template inválido para esta conta.');
        }

        $order = null;
        if (!empty($validated['use_real_data']) && !empty($validated['order_id'])) {
            $order = Pedido::where('loja_id', $loja->id)->with('cliente')->findOrFail((int) $validated['order_id']);
        }

        try {
            $this->settingsService->sendTemplateTest(
                loja: $loja,
                account: $account,
                template: $template,
                phone: $validated['phone'],
                eventKey: $validated['event_key'] ?: null,
                pedido: $order,
            );

            $this->settingsService->update($loja, ['last_test_phone' => $validated['phone']]);
        } catch (\Throwable $e) {
            return back()->withErrors(['whatsapp_test' => $this->friendlyError($e->getMessage())])->withInput();
        }

        return redirect()->route('admin.whatsapp.index')->with('sucesso', 'Mensagem teste enviada com sucesso. Você já pode acompanhar o status na inbox e nos logs.');
    }

    public function inbox(Request $request): View
    {
        $loja = Auth::user()->loja;
        $filters = [
            'unread' => $request->boolean('unread'),
            'linked_order' => $request->boolean('linked_order'),
            'assigned_to' => $request->query('assigned_to'),
        ];

        $allConversationsCount = WhatsAppConversation::where('loja_id', $loja->id)->count();

        return view('painel.whatsapp.inbox', [
            'conversations' => $this->conversationService->inbox(
                lojaId: $loja->id,
                status: (string) $request->query('status', 'all'),
                perPage: 20,
                filters: $filters,
            ),
            'responsaveis' => Usuario::where('loja_id', $loja->id)->where('ativo', true)->orderBy('nome')->get(),
            'filters' => $filters,
            'status' => (string) $request->query('status', 'all'),
            'allConversationsCount' => $allConversationsCount,
        ]);
    }

    public function conversation(Request $request, WhatsAppConversation $conversation): View
    {
        $this->authorizeConversation($conversation);

        $messageOrigin = (string) $request->query('message_origin', 'all');
        $query = $conversation->messages()->orderBy('created_at');

        if ($messageOrigin === 'automated') {
            $query->where('is_automated', true);
        }

        if ($messageOrigin === 'human') {
            $query->where('is_automated', false);
        }

        $messages = $query->paginate(40)->withQueryString();
        $this->conversationService->markRead($conversation);

        return view('painel.whatsapp.conversation', [
            'conversation' => $conversation->load(['cliente', 'pedido', 'account', 'assignedTo']),
            'messages' => $messages,
            'messageOrigin' => $messageOrigin,
            'templates' => $conversation->account->templates()->orderBy('name')->get(),
        ]);
    }

    public function sendConversationMessage(Request $request, WhatsAppConversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'type' => ['required', 'in:text,template'],
            'body' => ['required_if:type,text', 'nullable', 'string', 'max:4096'],
            'template_name' => ['required_if:type,template', 'nullable', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            if ($validated['type'] === 'text') {
                $this->messageService->sendText($conversation->account, $conversation, $validated['body'], Auth::id());
            } else {
                $this->messageService->sendTemplate(
                    account: $conversation->account,
                    conversation: $conversation,
                    templateName: $validated['template_name'],
                    languageCode: $validated['language'] ?? 'pt_BR',
                    components: [],
                    sentByUserId: Auth::id(),
                );
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['conversation_send' => $this->friendlyError($e->getMessage())])->withInput();
        }

        return redirect()->route('admin.whatsapp.page.conversation', $conversation)->with('sucesso', 'Mensagem enviada para a fila.');
    }

    public function logs(Request $request): View
    {
        $lojaId = Auth::user()->loja_id;
        $messageStatus = (string) $request->query('message_status', 'all');

        $messagesQuery = WhatsAppMessage::with(['conversation', 'account', 'sentBy'])
            ->where('loja_id', $lojaId)
            ->orderByDesc('created_at');

        if ($messageStatus !== 'all') {
            $messagesQuery->where('status', $messageStatus);
        }

        return view('painel.whatsapp.logs', [
            'messages' => $messagesQuery->paginate(25, ['*'], 'messages_page')->withQueryString(),
            'webhookEvents' => WhatsAppWebhookEvent::where('loja_id', $lojaId)
                ->orderByDesc('created_at')
                ->paginate(25, ['*'], 'webhooks_page')
                ->withQueryString(),
            'messageStatus' => $messageStatus,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $loja  = Auth::user()->loja;
        $inicio = $request->query('inicio', now()->startOfMonth()->toDateString());
        $fim    = $request->query('fim', now()->toDateString());

        $metrics = $this->dashboardService->metricsForLoja($loja->id, $inicio, $fim);

        return view('painel.whatsapp.dashboard', [
            'metrics'  => $metrics,
            'inicio'   => $inicio,
            'fim'      => $fim,
        ]);
    }

    public function storeConversationNote(Request $request, WhatsAppConversation $conversation): RedirectResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        WhatsAppConversationNote::create([
            'loja_id'         => Auth::user()->loja_id,
            'conversation_id' => $conversation->id,
            'user_id'         => Auth::id(),
            'note'            => $validated['note'],
        ]);

        return back()->with('sucesso', 'Nota interna adicionada.');
    }

    private function authorizeAccount(WhatsAppAccount $account): void
    {
        if ($account->loja_id !== Auth::user()->loja_id) {
            abort(403, 'Acesso negado.');
        }
    }

    private function authorizeConversation(WhatsAppConversation $conversation): void
    {
        if ($conversation->loja_id !== Auth::user()->loja_id) {
            abort(403, 'Acesso negado.');
        }
    }

    private function renderPreviewMessage(string $eventKey, array $variables, array $context): string
    {
        $fallbackTemplates = [
            WhatsAppTemplate::KEY_ORDER_CREATED => 'Olá {{cliente_nome}}, seu pedido {{pedido_numero}} foi recebido com sucesso.',
            WhatsAppTemplate::KEY_ORDER_QUOTE_SENT => 'Olá {{cliente_nome}}, seu orçamento do pedido {{pedido_numero}} ficou em {{orcamento_valor}}.',
            WhatsAppTemplate::KEY_PAYMENT_CONFIRMED => 'Pagamento confirmado! O pedido {{pedido_numero}} já está em andamento.',
            WhatsAppTemplate::KEY_ORDER_PRODUCTION => 'Olá {{cliente_nome}}, seu pedido {{pedido_numero}} está {{status_pedido}}.',
            WhatsAppTemplate::KEY_ORDER_READY => 'Boa notícia: seu pedido {{pedido_numero}} está pronto para retirada.',
            WhatsAppTemplate::KEY_ORDER_DELIVERED => 'Pedido {{pedido_numero}} entregue. Obrigado por comprar com a gente!',
        ];

        $template = $fallbackTemplates[$eventKey] ?? 'Olá {{cliente_nome}}, atualização do seu pedido {{pedido_numero}}.';

        if ($variables === []) {
            $variables = ['cliente_nome', 'pedido_numero'];
        }

        foreach ($variables as $variableKey) {
            $template = str_replace('{{' . $variableKey . '}}', (string) ($context[$variableKey] ?? '-'), $template);
        }

        foreach (array_keys($context) as $ctxKey) {
            $template = str_replace('{{' . $ctxKey . '}}', (string) ($context[$ctxKey] ?? '-'), $template);
        }

        return $template;
    }

    private function friendlyError(string $rawMessage): string
    {
        $message = mb_strtolower($rawMessage);

        if (str_contains($message, 'opt-in')) {
            return 'Este número ainda não autorizou receber mensagens. Peça o opt-in antes de enviar.';
        }

        if (str_contains($message, 'janela de 24h')) {
            return 'A janela de 24h está fechada. Use um template aprovado para iniciar nova mensagem.';
        }

        if (str_contains($message, 'limite mensal')) {
            return 'Você atingiu o limite do plano para este mês. Faça upgrade para continuar enviando.';
        }

        if (str_contains($message, 'invalid') || str_contains($message, 'número')) {
            return 'Não foi possível enviar: revise o número informado e tente novamente.';
        }

        return 'Não foi possível concluir esta ação agora. Tente novamente em instantes.';
    }
}