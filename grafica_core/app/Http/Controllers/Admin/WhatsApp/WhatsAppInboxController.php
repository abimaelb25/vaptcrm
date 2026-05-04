<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Services\WhatsApp\WhatsAppConversationService;
use App\Services\WhatsApp\WhatsAppMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * WhatsApp Inbox controller.
 *
 * All routes are prefixed with /painel/whatsapp/inbox and gated by:
 *  - auth middleware
 *  - check_plan_feature:modulo_whatsapp
 *  - tenancy (loja_id from Auth::user()->loja_id)
 */
class WhatsAppInboxController extends Controller
{
    public function __construct(
        private WhatsAppConversationService $conversationService,
        private WhatsAppMessageService      $messageService,
    ) {}

    // -------------------------------------------------------------------------
    // Inbox list
    // -------------------------------------------------------------------------

    /**
     * GET /painel/whatsapp/inbox
     * Returns paginated conversation list for the authenticated loja.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'open');
        $lojaId = Auth::user()->loja_id;

        $conversations = $this->conversationService->inbox($lojaId, $status, 25, [
            'unread' => $request->boolean('unread'),
            'linked_order' => $request->boolean('linked_order'),
            'assigned_to' => $request->query('assigned_to'),
        ]);

        return response()->json($conversations);
    }

    // -------------------------------------------------------------------------
    // Single conversation
    // -------------------------------------------------------------------------

    /**
     * GET /painel/whatsapp/inbox/{conversation}
     */
    public function show(WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $conversation->load(['cliente', 'pedido', 'assignedTo', 'account']);

        return response()->json($conversation);
    }

    /**
     * GET /painel/whatsapp/inbox/{conversation}/messages
     */
    public function messages(WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $messages = $this->conversationService->messageHistory($conversation);

        // Mark conversation as read
        $this->conversationService->markRead($conversation);

        return response()->json($messages);
    }

    // -------------------------------------------------------------------------
    // Send message
    // -------------------------------------------------------------------------

    /**
     * POST /painel/whatsapp/inbox/{conversation}/send
     */
    public function send(Request $request, WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'type'          => 'required|in:text,template,image,document',
            'body'          => 'required_if:type,text|nullable|string|max:4096',
            'template_name' => 'required_if:type,template|nullable|string|max:120',
            'language'      => 'nullable|string|max:10',
            'components'    => 'nullable|array',
            'media_url'     => 'required_if:type,image,document|nullable|url|max:500',
            'caption'       => 'nullable|string|max:1024',
        ]);

        $account = $conversation->account;
        $userId  = Auth::id();

        $message = match ($validated['type']) {
            'text' => $this->messageService->sendText(
                $account, $conversation, $validated['body'], $userId
            ),
            'template' => $this->messageService->sendTemplate(
                $account,
                $conversation,
                $validated['template_name'],
                $validated['language'] ?? 'pt_BR',
                $validated['components'] ?? [],
                $userId
            ),
            'image', 'document' => $this->messageService->sendMedia(
                $account,
                $conversation,
                $validated['type'],
                $validated['media_url'],
                $validated['caption'] ?? null,
                $userId
            ),
        };

        return response()->json($message, 201);
    }

    // -------------------------------------------------------------------------
    // Conversation actions
    // -------------------------------------------------------------------------

    /**
     * PATCH /painel/whatsapp/inbox/{conversation}/assign
     */
    public function assign(Request $request, WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate(['user_id' => 'required|integer|exists:usuarios,id']);

        $this->conversationService->assignTo($conversation, $validated['user_id']);

        return response()->json(['message' => 'Atribuído com sucesso.']);
    }

    /**
     * PATCH /painel/whatsapp/inbox/{conversation}/resolve
     */
    public function resolve(WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);
        $this->conversationService->resolve($conversation);

        return response()->json(['message' => 'Conversa encerrada.']);
    }

    /**
     * PATCH /painel/whatsapp/inbox/{conversation}/reopen
     */
    public function reopen(WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);
        $this->conversationService->reopen($conversation);

        return response()->json(['message' => 'Conversa reaberta.']);
    }

    /**
     * PATCH /painel/whatsapp/inbox/{conversation}/link-cliente
     */
    public function linkCliente(Request $request, WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate(['cliente_id' => 'required|integer|exists:clientes,id']);

        $this->conversationService->linkCliente($conversation, $validated['cliente_id']);

        return response()->json(['message' => 'Cliente vinculado.']);
    }

    /**
     * PATCH /painel/whatsapp/inbox/{conversation}/link-pedido
     */
    public function linkPedido(Request $request, WhatsAppConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate(['pedido_id' => 'required|integer|exists:pedidos,id']);

        $this->conversationService->linkPedido($conversation, $validated['pedido_id']);

        return response()->json(['message' => 'Pedido vinculado.']);
    }

    public function updatePriority(Request $request, WhatsAppConversation $conversation): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $conversation->update(['priority' => $validated['priority']]);

        return back()->with('sucesso', 'Prioridade atualizada.');
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function authorizeConversation(WhatsAppConversation $conversation): void
    {
        if ($conversation->loja_id !== Auth::user()->loja_id) {
            abort(403, 'Acesso negado.');
        }
    }
}
