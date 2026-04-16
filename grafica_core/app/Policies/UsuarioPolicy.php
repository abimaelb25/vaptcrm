<?php

declare(strict_types=1);

namespace App\Policies;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:05
*/

use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class UsuarioPolicy
{
    /**
     * Define quem pode gerenciar funcionários.
     */
    public function viewAny(Usuario $user): bool
    {
        // Administrador tem controle total.
        // Atendente pode ver lista para atribuir pedidos (opcional, vamos permitir).
        return in_array($user->perfil, ['administrador', 'atendente'], true);
    }

    /**
     * Visualizar perfil individual.
     */
    public function view(Usuario $user, Usuario $model): bool
    {
        return $user->id === $model->id || $user->perfil === 'administrador';
    }

    /**
     * Criar novos funcionários.
     */
    public function create(Usuario $user): bool
    {
        return $user->perfil === 'administrador';
    }

    /**
     * Editar perfil: Apenas o dono do perfil ou Administradores.
     */
    public function update(Usuario $user, Usuario $model): bool
    {
        return $user->id === $model->id || $user->perfil === 'administrador';
    }

    /**
     * Excluir funcionários.
     */
    public function delete(Usuario $user, Usuario $model): bool
    {
        return $user->perfil === 'administrador' && $user->id !== $model->id;
    }
}
