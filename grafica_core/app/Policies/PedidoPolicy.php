<?php

declare(strict_types=1);

namespace App\Policies;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:07
*/

use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class PedidoPolicy
{
    /**
     * Todos os perfis operacionais podem ver a lista de pedidos.
     */
    public function viewAny(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'atendente', 'produção', 'financeiro'], true);
    }

    /**
     * Visualizar detalhes de um pedido específico.
     * Reforça isolamento de tenant além do global scope HasTenancy.
     */
    public function view(Usuario $user, Pedido $pedido): bool
    {
        return $this->viewAny($user)
            && (int) $pedido->loja_id === (int) $user->loja_id;
    }

    /**
     * Criar novos pedidos (Comercial/Balcão).
     */
    public function create(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'atendente'], true);
    }

    /**
     * Editar pedidos.
     */
    public function update(Usuario $user, Pedido $pedido): bool
    {
        // Isolamento de tenant obrigatório — defesa em profundidade
        if ((int) $pedido->loja_id !== (int) $user->loja_id) {
            return false;
        }

        // Administrador e Atendente podem editar tudo.
        if (in_array($user->perfil, ['administrador', 'atendente'], true)) {
            return true;
        }

        // Produção pode atualizar status para 'em_producao', 'pronto', etc.
        // Financeiro pode atualizar status de pagamento/faturamento.
        return in_array($user->perfil, ['produção', 'financeiro'], true);
    }

    /**
     * Excluir pedidos (Apenas Admin).
     */
    public function delete(Usuario $user, Pedido $pedido): bool
    {
        return $user->perfil === 'administrador'
            && (int) $pedido->loja_id === (int) $user->loja_id;
    }

    /**
     * Converter orçamento em pedido.
     */
    public function converter(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'atendente'], true);
    }

    /**
     * Validar pagamento (Financeiro).
     */
    public function marcarPago(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'financeiro', 'atendente'], true);
    }
}
