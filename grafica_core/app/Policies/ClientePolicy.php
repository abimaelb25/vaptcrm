<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cliente;
use App\Models\Usuario;

class ClientePolicy
{
    public function viewAny(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'atendente', 'financeiro'], true);
    }

    public function view(Usuario $user, Cliente $cliente): bool
    {
        return $this->viewAny($user)
            && (int) $cliente->loja_id === (int) $user->loja_id;
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'atendente'], true);
    }

    public function update(Usuario $user, Cliente $cliente): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente', 'atendente'], true)
            && (int) $cliente->loja_id === (int) $user->loja_id;
    }

    public function delete(Usuario $user, Cliente $cliente): bool
    {
        return $user->perfil === 'administrador'
            && (int) $cliente->loja_id === (int) $user->loja_id;
    }
}
