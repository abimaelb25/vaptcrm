<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FinancialTitle;
use App\Models\Usuario;

class FinancialTitlePolicy
{
    /** Perfis autorizados a operar títulos financeiros. */
    private const PERFIS_FINANCEIROS = ['administrador', 'gerente', 'financeiro'];

    public function viewAny(Usuario $user): bool
    {
        return in_array($user->perfil, self::PERFIS_FINANCEIROS, true);
    }

    public function view(Usuario $user, FinancialTitle $title): bool
    {
        // Verifica perfil E isolamento de tenant — defesa em profundidade caso
        // o global scope HasTenancy seja bypassado (withoutGlobalScopes, queues, etc.)
        return $this->viewAny($user)
            && (int) $title->loja_id === (int) $user->loja_id;
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->perfil, self::PERFIS_FINANCEIROS, true);
    }

    public function update(Usuario $user, FinancialTitle $title): bool
    {
        return in_array($user->perfil, self::PERFIS_FINANCEIROS, true)
            && (int) $title->loja_id === (int) $user->loja_id;
    }

    public function delete(Usuario $user, FinancialTitle $title): bool
    {
        return $user->perfil === 'administrador'
            && (int) $title->loja_id === (int) $user->loja_id;
    }
}
