<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Insumo;
use App\Models\Usuario;
use App\Services\Domain\InsumoPermissaoService;

/**
 * InsumoPolicy
 *
 * Controla permissões de acesso e ações no módulo de Insumos.
 * Delegado ao serviço InsumoPermissaoService que centraliza a lógica.
 *
 * Regras:
 * - Multi-tenant obrigatório em TUDO
 * - Exclusão física: APENAS admin, sem movimentações
 * - Com movimentações: FORÇAR inativação
 */
class InsumoPolicy
{
    public function __construct(
        private readonly InsumoPermissaoService $permissaoService,
    ) {}

    public function viewAny(Usuario $user): bool
    {
        // Qualquer usuário autorizado pode listar
        return in_array($user->perfil, ['administrador', 'gerente', 'operador', 'produção'], true);
    }

    public function view(Usuario $user, Insumo $insumo): bool
    {
        return $this->permissaoService->canView($user, $insumo);
    }

    public function create(Usuario $user): bool
    {
        return in_array($user->perfil, ['administrador', 'gerente'], true);
    }

    public function update(Usuario $user, Insumo $insumo): bool
    {
        return $this->permissaoService->canEdit($user, $insumo);
    }

    /**
     * Exclusão física (delete permanente).
     *
     * Apenas administrador, e apenas se não houver movimentações.
     * Se houver movimentações, use restore() ou deactivate().
     */
    public function delete(Usuario $user, Insumo $insumo): bool
    {
        try {
            return $this->permissaoService->canDelete($user, $insumo);
        } catch (\RuntimeException $e) {
            // Exceção indica que deve inativar, não excluir
            return false;
        }
    }

    /**
     * Inativação (soft-delete).
     *
     * Alternativa segura à exclusão física.
     * Permite que gestores desativem insumos mesmo com movimentações.
     */
    public function deactivate(Usuario $user, Insumo $insumo): bool
    {
        return $this->permissaoService->canDeactivate($user, $insumo);
    }

    /**
     * Restauração (undelete).
     *
     * Apenas administrador pode restaurar.
     */
    public function restore(Usuario $user, Insumo $insumo): bool
    {
        return (int) $user->loja_id === (int) $insumo->loja_id
            && $user->perfil === 'administrador';
    }

    /**
     * Exclusão permanente de restaurado.
     */
    public function forceDelete(Usuario $user, Insumo $insumo): bool
    {
        // Apenas admin, mesmo que haja movimentações (última linha de defesa)
        return (int) $user->loja_id === (int) $insumo->loja_id
            && $user->perfil === 'administrador';
    }
}
