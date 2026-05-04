<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Insumo;
use App\Models\EstoqueMovimentacao;
use App\Models\Loja;
use App\Models\Usuario;
use App\Services\Domain\InsumoPermissaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de Governança e Permissões: Módulo Insumos
 */
final class InsumoPermissaoTest extends TestCase
{
    use RefreshDatabase;

    protected Loja $loja;
    protected Loja $outraLoja;
    protected Usuario $admin;
    protected Usuario $gerente;
    protected Usuario $operador;
    protected InsumoPermissaoService $permissaoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loja = Loja::factory()->create();
        $this->outraLoja = Loja::factory()->create();

        $this->admin = Usuario::factory()->create([
            'loja_id' => $this->loja->id,
            'perfil' => 'administrador',
        ]);
        $this->gerente = Usuario::factory()->create([
            'loja_id' => $this->loja->id,
            'perfil' => 'gerente',
        ]);
        $this->operador = Usuario::factory()->create([
            'loja_id' => $this->loja->id,
            'perfil' => 'operador',
        ]);

        $this->permissaoService = app(InsumoPermissaoService::class);
    }

    /**
     * Teste: Multi-tenant é respeitado - usuário não acessa insumo de outra loja.
     */
    public function test_multi_tenant_prevents_cross_shop_access(): void
    {
        $insumo = Insumo::factory()->create(['loja_id' => $this->outraLoja->id]);

        // Admin de loja A não pode ver insumo de loja B
        $this->assertFalse($this->permissaoService->canView($this->admin, $insumo));
        $this->assertFalse($this->permissaoService->canEdit($this->admin, $insumo));
        $this->assertFalse($this->permissaoService->canAdjustStock($this->admin, $insumo));
    }

    /**
     * Teste: Operador pode visualizar mas não editar.
     */
    public function test_operador_can_view_but_not_edit(): void
    {
        $insumo = Insumo::factory()->create(['loja_id' => $this->loja->id]);

        $this->assertTrue($this->permissaoService->canView($this->operador, $insumo));
        $this->assertFalse($this->permissaoService->canEdit($this->operador, $insumo));
        $this->assertFalse($this->permissaoService->canAdjustStock($this->operador, $insumo));
    }

    /**
     * Teste: Gerente pode editar e ajustar.
     */
    public function test_gerente_can_edit_and_adjust(): void
    {
        $insumo = Insumo::factory()->create(['loja_id' => $this->loja->id]);

        $this->assertTrue($this->permissaoService->canView($this->gerente, $insumo));
        $this->assertTrue($this->permissaoService->canEdit($this->gerente, $insumo));
        $this->assertTrue($this->permissaoService->canAdjustStock($this->gerente, $insumo));
        $this->assertTrue($this->permissaoService->canDeactivate($this->gerente, $insumo));
    }

    /**
     * Teste: Exclusão física - APENAS admin, APENAS sem movimentações.
     */
    public function test_exclusao_fisica_apenas_admin_sem_movimentacoes(): void
    {
        $insumoSemMovimentacao = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'pode_ser_excluido' => true,
        ]);

        // Gerente NÃO pode excluir fisicamente
        $this->assertFalse($this->permissaoService->canDelete($this->gerente, $insumoSemMovimentacao));

        // Admin pode excluir se sem movimentações
        $this->assertTrue($this->permissaoService->canDelete($this->admin, $insumoSemMovimentacao));
    }

    /**
     * Teste: Com movimentações, exclusão física é bloqueada.
     */
    public function test_com_movimentacoes_exclusao_bloqueada(): void
    {
        $insumo = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'pode_ser_excluido' => true,
        ]);

        // Criar uma movimentação
        EstoqueMovimentacao::factory()->create(['insumo_id' => $insumo->id]);

        // Tenta excluir deve lançar exceção
        $this->expectException(\RuntimeException::class);
        $this->permissaoService->canDelete($this->admin, $insumo);
    }

    /**
     * Teste: Com movimentações, apenas inativação é permitida.
     */
    public function test_com_movimentacoes_apenas_inativacao(): void
    {
        $insumo = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'ativo' => true,
        ]);

        // Criar uma movimentação
        EstoqueMovimentacao::factory()->create(['insumo_id' => $insumo->id]);

        // Inativação permitida
        $this->assertTrue($this->permissaoService->canDeactivate($this->gerente, $insumo));

        // Exclusão bloqueada
        $this->expectException(\RuntimeException::class);
        $this->permissaoService->canDelete($this->admin, $insumo);
    }

    /**
     * Teste: Ação recomendada (delete vs deactivate).
     */
    public function test_removal_action_recomendation(): void
    {
        // Sem movimentação: admin pode excluir
        $insumoLimpo = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'pode_ser_excluido' => true,
        ]);
        $this->assertEquals('delete', $this->permissaoService->getRemovalAction($this->admin, $insumoLimpo));

        // Gerente sem movimentação: inativar
        $this->assertEquals('deactivate', $this->permissaoService->getRemovalAction($this->gerente, $insumoLimpo));

        // Com movimentação: sempre inativar
        $insumoComMovimentacao = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'ativo' => true,
        ]);
        EstoqueMovimentacao::factory()->create(['insumo_id' => $insumoComMovimentacao->id]);

        $this->assertEquals('deactivate', $this->permissaoService->getRemovalAction($this->admin, $insumoComMovimentacao));
        $this->assertEquals('deactivate', $this->permissaoService->getRemovalAction($this->gerente, $insumoComMovimentacao));
    }

    /**
     * Teste: Operador não tem ação de remoção.
     */
    public function test_operador_sem_acesso_remocao(): void
    {
        $insumo = Insumo::factory()->create(['loja_id' => $this->loja->id]);

        $this->assertNull($this->permissaoService->getRemovalAction($this->operador, $insumo));
    }

    /**
     * Teste: Flag pode_ser_excluido bloqueia exclusão.
     */
    public function test_flag_pode_ser_excluido_bloqueia_exclusao(): void
    {
        $insumo = Insumo::factory()->create([
            'loja_id' => $this->loja->id,
            'pode_ser_excluido' => false, // Bloqueado
        ]);

        $this->expectException(\RuntimeException::class);
        $this->permissaoService->canDelete($this->admin, $insumo);
    }
}
