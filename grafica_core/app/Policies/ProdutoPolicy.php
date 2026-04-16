<?php

declare(strict_types=1);

namespace App\Policies;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:09
*/

use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class ProdutoPolicy
{
    /**
     * Visualizar lista de produtos.
     */
    public function viewAny(Usuario $user): bool
    {
        return true; // Todos os funcionários do painel podem ver produtos.
    }

    /**
     * Visualizar um produto.
     */
    public function view(Usuario $user, Produto $produto): bool
    {
        return true;
    }

    /**
     * Criar produtos.
     */
    public function create(Usuario $user): bool
    {
        return $user->perfil === 'administrador';
    }

    /**
     * Editar produtos.
     */
    public function update(Usuario $user, Produto $produto): bool
    {
        return $user->perfil === 'administrador';
    }

    /**
     * Excluir produtos.
     */
    public function delete(Usuario $user, Produto $produto): bool
    {
        return $user->perfil === 'administrador';
    }
}
