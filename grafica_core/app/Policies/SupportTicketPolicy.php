<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\Usuario;

class SupportTicketPolicy
{
    /**
     * Administrador e gerente veem todos os tickets da loja.
     * Demais perfis veem apenas os próprios tickets (filtrado no controller).
     */
    public function viewAny(Usuario $user): bool
    {
        return true; // qualquer autenticado do painel pode acessar a listagem
    }

    /**
     * Visualizar um ticket específico.
     * Admin/gerente podem ver qualquer ticket da loja.
     * Demais usuários apenas os seus próprios.
     */
    public function view(Usuario $user, SupportTicket $ticket): bool
    {
        // Isolamento de tenant obrigatório
        if ((int) $ticket->loja_id !== (int) $user->loja_id) {
            return false;
        }

        if (in_array($user->perfil, ['administrador', 'gerente'], true)) {
            return true;
        }

        return (int) $ticket->user_id === (int) $user->id;
    }

    /**
     * Qualquer usuário autenticado do painel pode abrir ticket.
     */
    public function create(Usuario $user): bool
    {
        return true;
    }

    /**
     * Responder a um ticket — mesma regra de visualização.
     * Mapeia para o ability 'update' do resource, reutilizado também em reply().
     */
    public function update(Usuario $user, SupportTicket $ticket): bool
    {
        if ((int) $ticket->loja_id !== (int) $user->loja_id) {
            return false;
        }

        if (in_array($user->perfil, ['administrador', 'gerente'], true)) {
            return true;
        }

        return (int) $ticket->user_id === (int) $user->id;
    }

    /**
     * Excluir ticket — somente administrador, mesmo tenant.
     */
    public function delete(Usuario $user, SupportTicket $ticket): bool
    {
        return $user->perfil === 'administrador'
            && (int) $ticket->loja_id === (int) $user->loja_id;
    }
}
