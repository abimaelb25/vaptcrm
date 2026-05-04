<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Insumo;
use App\Models\Usuario;

/**
 * InsumoPermissaoService
 *
 * Centraliza todas as regras de permissão para operações com insumos:
 * - Visualização
 * - Edição
 * - Ajuste de saldo
 * - Registro de entrada
 * - Exclusão (física)
 * - Inativação
 *
 * Regras:
 * - Operador: pode visualizar e ajustar saldo
 * - Gestor/Gerente: pode fazer tudo exceto excluir
 * - Admin: pode fazer tudo incluindo exclusão, mas com validações
 * - Multi-tenant: SEMPRE filtrar por loja_id
 * - Exclusão física: APENAS se sem movimentações
 * - Com movimentações: FORÇAR inativação
 */
final class InsumoPermissaoService
{
    public const PERFIL_ADMINISTRADOR = 'administrador';
    public const PERFIL_GERENTE = 'gerente';
    public const PERFIL_OPERADOR = 'operador';
    public const PERFIL_PRODUCAO = 'produção';

    public const PERFIS_EDICAO = [self::PERFIL_ADMINISTRADOR, self::PERFIL_GERENTE];
    public const PERFIS_VISUALIZACAO = [
        self::PERFIL_ADMINISTRADOR,
        self::PERFIL_GERENTE,
        self::PERFIL_OPERADOR,
        self::PERFIL_PRODUCAO,
    ];

    /**
     * Valida se usuário pode visualizar insumo.
     */
    public function canView(Usuario $user, Insumo $insumo): bool
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return false;
        }

        return in_array($user->perfil, self::PERFIS_VISUALIZACAO, true);
    }

    /**
     * Valida se usuário pode editar insumo (nome, categoria, unidades, etc).
     */
    public function canEdit(Usuario $user, Insumo $insumo): bool
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return false;
        }

        return in_array($user->perfil, self::PERFIS_EDICAO, true);
    }

    /**
     * Valida se usuário pode registrar entrada de estoque.
     */
    public function canRegisterEntry(Usuario $user): bool
    {
        return in_array($user->perfil, self::PERFIS_EDICAO, true);
    }

    /**
     * Valida se usuário pode ajustar saldo (contagem física).
     */
    public function canAdjustStock(Usuario $user, Insumo $insumo): bool
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return false;
        }

        // Apenas gestor/admin
        return in_array($user->perfil, self::PERFIS_EDICAO, true);
    }

    /**
     * Valida se usuário pode EXCLUIR FISICAMENTE um insumo.
     *
     * Regras:
     * - Apenas administrador
     * - APENAS se não houver movimentações
     * - APENAS se não houver vínculos críticos
     *
     * Se houver movimentações, o método `canDeactivate()` deveria ser usado.
     *
     * @return bool True se pode excluir, false caso contrário
     * @throws \RuntimeException Se houver movimentações (use inativação)
     */
    public function canDelete(Usuario $user, Insumo $insumo): bool
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return false;
        }

        // Apenas administrador pode excluir fisicamente
        if ($user->perfil !== self::PERFIL_ADMINISTRADOR) {
            return false;
        }

        // Verifica se há movimentações
        if ($insumo->movimentacoes()->count() > 0) {
            throw new \RuntimeException(
                "Não é possível excluir \"{$insumo->nome}\" pois existem movimentações de estoque registradas. "
                . "Use a ação 'Inativar' para desativar o insumo mantendo o histórico."
            );
        }

        // Verifica se há vínculos em fichas técnicas de produtos (se implementado)
        // if ($insumo->produtoMateriais()->count() > 0) {
        //     throw new \RuntimeException("Não é possível excluir pois há produtos usando este insumo.");
        // }

        // Flag de segurança adicional
        if (!$insumo->pode_ser_excluido) {
            throw new \RuntimeException(
                "Este insumo foi marcado como não-excluível por razões de auditoria ou dependências. "
                . "Apenas inativação é permitida."
            );
        }

        return true;
    }

    /**
     * Valida se usuário pode INATIVAR um insumo.
     *
     * Inativação é a ação preferida quando há movimentações, permitindo:
     * - Preservar histórico
     * - Impedir uso em novos lançamentos
     * - Manter integridade de dados
     *
     * Pode ser feito por gestor/admin.
     */
    public function canDeactivate(Usuario $user, Insumo $insumo): bool
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return false;
        }

        // Gestor ou admin
        return in_array($user->perfil, self::PERFIS_EDICAO, true);
    }

    /**
     * Determina qual ação de remoção exibir na UI.
     *
     * Retorna:
     * - 'delete' se pode excluir fisicamente
     * - 'deactivate' se deve inativar
     * - null se não pode fazer nenhuma das duas
     */
    public function getRemovalAction(Usuario $user, Insumo $insumo): ?string
    {
        // Multi-tenant obrigatório
        if ((int) $user->loja_id !== (int) $insumo->loja_id) {
            return null;
        }

        // Apenas admin/gerente
        if (!in_array($user->perfil, self::PERFIS_EDICAO, true)) {
            return null;
        }

        // Se há movimentações ou insumo está marcado como não-excluível
        if ($insumo->movimentacoes()->count() > 0 || !$insumo->pode_ser_excluido) {
            return $this->canDeactivate($user, $insumo) ? 'deactivate' : null;
        }

        // Apenas admin pode excluir fisicamente
        return $user->perfil === self::PERFIL_ADMINISTRADOR ? 'delete' : 'deactivate';
    }

    /**
     * Retorna um resumo legível das ações permitidas para este usuário neste insumo.
     */
    public function getPermissionsForDisplay(Usuario $user, Insumo $insumo): array
    {
        return [
            'can_view' => $this->canView($user, $insumo),
            'can_edit' => $this->canEdit($user, $insumo),
            'can_adjust_stock' => $this->canAdjustStock($user, $insumo),
            'can_register_entry' => $this->canRegisterEntry($user),
            'can_deactivate' => $this->canDeactivate($user, $insumo),
            'can_delete' => false, // Nunca exibir como true explicitamente, usar getRemovalAction()
            'removal_action' => $this->getRemovalAction($user, $insumo),
        ];
    }
}
