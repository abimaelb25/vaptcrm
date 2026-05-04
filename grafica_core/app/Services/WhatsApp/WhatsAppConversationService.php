<?php

declare(strict_types=1);

namespace App\Services\WhatsApp;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppConversation;
use App\Models\WhatsApp\WhatsAppMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Manages the WhatsApp Inbox: conversation threads, assignment and resolution.
 */
class WhatsAppConversationService
{
    // -------------------------------------------------------------------------
    // Conversation lifecycle
    // -------------------------------------------------------------------------

    /**
     * Find or create a conversation for an inbound message.
     * Extends the 24h service window on every inbound message.
     */
    public function findOrCreateFromInbound(
        WhatsAppAccount $account,
        string $fromPhone,
        ?string $contactName = null
    ): WhatsAppConversation {
        return DB::transaction(function () use ($account, $fromPhone, $contactName) {
            $conversation = WhatsAppConversation::withTrashed()
                ->where('whatsapp_account_id', $account->id)
                ->where('contact_phone', $fromPhone)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if ($conversation === null) {
                $conversation = WhatsAppConversation::create([
                    'loja_id'              => $account->loja_id,
                    'whatsapp_account_id'  => $account->id,
                    'contact_phone'        => $fromPhone,
                    'contact_name'         => $contactName,
                    'status'               => WhatsAppConversation::STATUS_OPEN,
                    'window_expires_at'    => now()->addHours(24),
                    'last_message_at'      => now(),
                    'is_unread'            => true,
                ]);

                // Auto-link to cliente if phone matches
                $this->autoLinkCliente($conversation);
            } else {
                // Refresh service window
                $conversation->update([
                    'window_expires_at' => now()->addHours(24),
                    'last_message_at'   => now(),
                    'is_unread'         => true,
                    'status'            => WhatsAppConversation::STATUS_OPEN,
                ]);
                if ($contactName && $conversation->contact_name === null) {
                    $conversation->update(['contact_name' => $contactName]);
                }
            }

            return $conversation;
        });
    }

    public function assignTo(WhatsAppConversation $conversation, int $userId): void
    {
        $conversation->update(['assigned_to' => $userId]);
    }

    public function resolve(WhatsAppConversation $conversation): void
    {
        $conversation->update(['status' => WhatsAppConversation::STATUS_RESOLVED]);
    }

    public function reopen(WhatsAppConversation $conversation): void
    {
        $conversation->update(['status' => WhatsAppConversation::STATUS_OPEN]);
    }

    public function markRead(WhatsAppConversation $conversation): void
    {
        $conversation->update(['is_unread' => false]);
    }

    // -------------------------------------------------------------------------
    // Linking
    // -------------------------------------------------------------------------

    public function linkCliente(WhatsAppConversation $conversation, int $clienteId): void
    {
        $conversation->update(['cliente_id' => $clienteId]);
    }

    public function linkPedido(WhatsAppConversation $conversation, int $pedidoId): void
    {
        $conversation->update(['pedido_id' => $pedidoId]);
    }

    // -------------------------------------------------------------------------
    // Inbox queries
    // -------------------------------------------------------------------------

    /**
     * Paginated inbox list for a loja, most recent first.
     */
    public function inbox(
        int $lojaId,
        string $status = 'open',
        int $perPage = 25,
        array $filters = []
    ): LengthAwarePaginator {
        $query = WhatsAppConversation::with(['cliente', 'assignedTo', 'account'])
            ->forLoja($lojaId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($filters['unread'])) {
            $query->where('is_unread', true);
        }

        if (!empty($filters['linked_order'])) {
            $query->whereNotNull('pedido_id');
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', (int) $filters['assigned_to']);
        }

        return $query
            ->orderByDesc('last_message_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Full message history for a conversation.
     */
    public function messageHistory(
        WhatsAppConversation $conversation,
        int $perPage = 50
    ): LengthAwarePaginator {
        return $conversation->messages()
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function autoLinkCliente(WhatsAppConversation $conversation): void
    {
        $cliente = Cliente::where('loja_id', $conversation->loja_id)
            ->where(function ($q) use ($conversation) {
                $q->where('whatsapp', $conversation->contact_phone)
                  ->orWhere('telefone', $conversation->contact_phone);
            })
            ->first();

        if ($cliente) {
            $conversation->update(['cliente_id' => $cliente->id]);
        }
    }
}
