<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FinancialTitle;
use App\Models\Usuario;

class FinancialTitlePolicy
{
    public function viewAny(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'financeiro'], true);
    }

    public function view(Usuario $user, FinancialTitle $title): bool
    {
        return $this->viewAny($user);
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'financeiro'], true);
    }

    public function update(Usuario $user, FinancialTitle $title): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'financeiro'], true);
    }

    public function delete(Usuario $user, FinancialTitle $title): bool
    {
        return $user->perfil === 'administrador';
    }
}
