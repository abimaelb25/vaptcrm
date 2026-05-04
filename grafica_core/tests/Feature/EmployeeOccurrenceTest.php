<?php

declare(strict_types=1);

namespace Tests\Feature;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:30
| Descrição: Testes para Ocorrências RH (multi-tenant, auditoria, validação)
*/

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeOccurrenceTest extends TestCase
{
    use RefreshDatabase;

    protected Loja $loja1;
    protected Loja $loja2;
    protected Usuario $admin;
    protected Employee $employee1;
    protected Employee $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup: duas lojas para testar isolamento
        $this->loja1 = Loja::factory()->create(['nome' => 'Loja 1']);
        $this->loja2 = Loja::factory()->create(['nome' => 'Loja 2']);

        // Admin autenticado
        $this->admin = Usuario::factory()->create([
            'nome' => 'Admin',
            'loja_id' => $this->loja1->id,
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        // Funcionários em lojas diferentes
        $this->employee1 = Employee::factory()->create([
            'loja_id' => $this->loja1->id,
            'nome_completo' => 'João da Silva',
            'status_funcional' => 'ativo',
        ]);

        $this->employee2 = Employee::factory()->create([
            'loja_id' => $this->loja2->id,
            'nome_completo' => 'Maria Santos',
            'status_funcional' => 'ativo',
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * Teste 1: Criar ocorrência com dados válidos
     */
    public function test_criar_ocorrencia_com_dados_validos()
    {
        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'advertencia',
                'subtipo' => 'verbal',
                'titulo' => 'Advertência Verbal',
                'descricao' => 'Atraso reiterado',
                'data_ocorrencia' => now()->toDateString(),
                'status' => 'registrada',
            ]
        );

        $response->assertRedirect(route('admin.system.equipe.ocorrencias.index', $this->employee1->id));
        $this->assertDatabaseHas('employee_occurrences', [
            'employee_id' => $this->employee1->id,
            'loja_id' => $this->loja1->id,
            'tipo' => 'advertencia',
            'titulo' => 'Advertência Verbal',
        ]);
    }

    /**
     * Teste 2: Validar isolamento multi-tenant
     * Um usuário não deve conseguir acessar ocorrências de outro tenant
     */
    public function test_isolamento_multi_tenant_ocorrencias()
    {
        // Criar ocorrência para employee1 (loja1)
        $ocorrencia1 = EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'falta',
            'titulo' => 'Falta Injustificada',
            'data_ocorrencia' => now(),
            'status' => 'registrada',
            'created_by' => $this->admin->id,
        ]);

        // Tentar editar ocorrência de outro tenant deve falhar
        $response = $this->put(
            route('admin.system.equipe.ocorrencias.update', [$this->employee2->id, $ocorrencia1->id]),
            ['status' => 'resolvida']
        );

        // Verifica se o acesso foi negado (403)
        $response->assertStatus(403);
    }

    /**
     * Teste 3: Validação de suspensão (requer datas)
     */
    public function test_suspensao_requer_datas_inicio_e_fim()
    {
        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'suspensao',
                'titulo' => 'Suspensão',
                'data_ocorrencia' => now()->toDateString(),
                'status' => 'registrada',
                // Sem data_inicio e data_fim
            ]
        );

        // Deve retornar erro de validação
        $response->assertSessionHasErrors();
    }

    /**
     * Teste 4: Desligamento atualiza status_funcional
     */
    public function test_desligamento_atualiza_status_funcional()
    {
        $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'desligamento',
                'subtipo' => 'pedido_demissao',
                'titulo' => 'Pedido de Demissão',
                'data_ocorrencia' => now()->toDateString(),
                'status' => 'registrada',
            ]
        );

        // Verifica se status foi atualizado
        $this->employee1->refresh();
        $this->assertEquals('desligado', $this->employee1->status_funcional);
        $this->assertNotNull($this->employee1->data_desligamento);
    }

    /**
     * Teste 5: Listagem de ocorrências do colaborador
     */
    public function test_listar_ocorrencias_do_colaborador()
    {
        // Criar 3 ocorrências para employee1
        EmployeeOccurrence::factory(3)->create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'created_by' => $this->admin->id,
        ]);

        // Criar 2 ocorrências para employee2 (outro tenant)
        EmployeeOccurrence::factory(2)->create([
            'loja_id' => $this->loja2->id,
            'employee_id' => $this->employee2->id,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(
            route('admin.system.equipe.ocorrencias.index', $this->employee1->id)
        );

        $response->assertViewHas('ocorrencias');
        $ocorrencias = $response->viewData('ocorrencias');
        
        // Deve listar apenas as 3 ocorrências do employee1
        $this->assertCount(3, $ocorrencias);
    }

    /**
     * Teste 6: Filtro por tipo de ocorrência
     */
    public function test_filtrar_ocorrencias_por_tipo()
    {
        // Criar mistura de tipos
        EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'advertencia',
            'subtipo' => 'verbal',
            'titulo' => 'Advertência Verbal',
            'data_ocorrencia' => now(),
            'created_by' => $this->admin->id,
        ]);

        EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'falta',
            'titulo' => 'Falta',
            'data_ocorrencia' => now(),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(
            route('admin.system.equipe.ocorrencias.index', $this->employee1->id) . '?tipo=advertencia'
        );

        $response->assertViewHas('ocorrencias');
        $ocorrencias = $response->viewData('ocorrencias');
        
        // Deve listar apenas advertências
        $this->assertCount(1, $ocorrencias);
        $this->assertEquals('advertencia', $ocorrencias->first()->tipo);
    }

    /**
     * Teste 7: Auditoria - created_by é preenchido automaticamente
     */
    public function test_auditoria_created_by_preenchido()
    {
        $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'advertencia',
                'subtipo' => 'escrita',
                'titulo' => 'Advertência Escrita',
                'data_ocorrencia' => now()->toDateString(),
            ]
        );

        $ocorrencia = EmployeeOccurrence::where('employee_id', $this->employee1->id)->first();
        $this->assertEquals($this->admin->id, $ocorrencia->created_by);
    }

    /**
     * Teste 8: Atualização de ocorrência registra updated_by
     */
    public function test_atualizacao_registra_updated_by()
    {
        $ocorrencia = EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'falta',
            'titulo' => 'Falta Inicial',
            'data_ocorrencia' => now(),
            'status' => 'registrada',
            'created_by' => $this->admin->id,
        ]);

        $this->patch(
            route('admin.system.equipe.ocorrencias.update', [$this->employee1->id, $ocorrencia->id]),
            ['status' => 'resolvida']
        );

        $ocorrencia->refresh();
        $this->assertEquals($this->admin->id, $ocorrencia->updated_by);
    }

    /**
     * Teste 9: Exclusão de ocorrência (soft delete)
     */
    public function test_exclusao_soft_delete()
    {
        $ocorrencia = EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'advertencia',
            'titulo' => 'Advertência',
            'data_ocorrencia' => now(),
            'created_by' => $this->admin->id,
        ]);

        $this->delete(
            route('admin.system.equipe.ocorrencias.destroy', [$this->employee1->id, $ocorrencia->id])
        );

        // Deve ser soft deleted
        $this->assertSoftDeleted('employee_occurrences', ['id' => $ocorrencia->id]);
    }

    /**
     * Teste 10: Métodos auxiliares do Model funcionam corretamente
     */
    public function test_metodos_auxiliares_model()
    {
        $ocorrencia = EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'advertencia',
            'subtipo' => 'verbal',
            'titulo' => 'Test',
            'data_ocorrencia' => now(),
            'data_inicio' => now()->addDays(1),
            'data_fim' => now()->addDays(3),
            'status' => 'registrada',
            'created_by' => $this->admin->id,
        ]);

        // Teste labels
        $this->assertEquals('Advertência', $ocorrencia->getTipoLabel());
        $this->assertEquals('Verbal', $ocorrencia->getSubtipoLabel());
        $this->assertEquals('Registrada', $ocorrencia->getStatusLabel());

        // Teste duração
        $this->assertEquals(2, $ocorrencia->getDuracao());

        // Teste isAtiva
        $this->assertTrue($ocorrencia->isAtiva());
        $ocorrencia->status = 'resolvida';
        $this->assertFalse($ocorrencia->isAtiva());
    }

    /**
     * Teste 11: Validação de subtipo (rejeita inválidos)
     */
    public function test_validacao_subtipo_advertencia()
    {
        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'advertencia',
                'subtipo' => 'invalido',
                'titulo' => 'Advertência',
                'data_ocorrencia' => now()->toDateString(),
            ]
        );

        // Deve retornar erro
        $response->assertStatus(422);
    }

    /**
     * Teste 12: Protege contra mass assignment de loja_id e employee_id
     */
    public function test_protege_contra_mass_assignment_sensivel()
    {
        $ocorrencia = EmployeeOccurrence::create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $this->employee1->id,
            'tipo' => 'falta',
            'titulo' => 'Test',
            'data_ocorrencia' => now(),
            'created_by' => $this->admin->id,
        ]);

        // Tentar atualizar employee_id via whitelist
        try {
            $ocorrencia->update(['employee_id' => $this->employee2->id]);
            $this->fail('Deveria ter lançado exceção ao tentar atualizar employee_id');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('relacionamento', $e->getMessage());
        }
    }

    /**
     * Teste 13: Funcionário não pode criar ocorrência (mesmo no próprio perfil)
     */
    public function test_funcionario_nao_pode_criar_ocorrencia_no_proprio_perfil()
    {
        $funcionarioUsuario = Usuario::factory()->create([
            'loja_id' => $this->loja1->id,
            'perfil' => 'atendente',
            'ativo' => true,
            'permissoes' => [],
        ]);

        $employeeProprio = Employee::factory()->create([
            'loja_id' => $this->loja1->id,
            'user_id' => $funcionarioUsuario->id,
        ]);

        $this->actingAs($funcionarioUsuario);

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $employeeProprio->id),
            [
                'tipo' => 'advertencia',
                'subtipo' => 'verbal',
                'titulo' => 'Tentativa indevida',
                'data_ocorrencia' => now()->toDateString(),
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('employee_occurrences', [
            'employee_id' => $employeeProprio->id,
            'titulo' => 'Tentativa indevida',
        ]);
    }

    /**
     * Teste 14: Funcionário pode visualizar somente as próprias ocorrências
     */
    public function test_funcionario_pode_visualizar_somente_proprias_ocorrencias()
    {
        $funcionarioUsuario = Usuario::factory()->create([
            'loja_id' => $this->loja1->id,
            'perfil' => 'atendente',
            'ativo' => true,
            'permissoes' => [],
        ]);

        $employeeProprio = Employee::factory()->create([
            'loja_id' => $this->loja1->id,
            'user_id' => $funcionarioUsuario->id,
        ]);

        $employeeOutro = Employee::factory()->create([
            'loja_id' => $this->loja1->id,
        ]);

        EmployeeOccurrence::factory()->create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $employeeProprio->id,
            'created_by' => $this->admin->id,
        ]);

        EmployeeOccurrence::factory()->create([
            'loja_id' => $this->loja1->id,
            'employee_id' => $employeeOutro->id,
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($funcionarioUsuario);

        $responseProprio = $this->get(route('admin.system.equipe.ocorrencias.index', $employeeProprio->id));
        $responseProprio->assertStatus(200);

        $responseOutro = $this->get(route('admin.system.equipe.ocorrencias.index', $employeeOutro->id));
        $responseOutro->assertStatus(403);
    }

    /**
     * Teste 15: Usuário com permissão RH pode criar ocorrência
     */
    public function test_usuario_com_permissao_rh_pode_criar_ocorrencia()
    {
        $usuarioRh = Usuario::factory()->create([
            'loja_id' => $this->loja1->id,
            'perfil' => 'atendente',
            'ativo' => true,
            'permissoes' => [
                'rh_ocorrencias_criar' => true,
            ],
        ]);

        $this->actingAs($usuarioRh);

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->employee1->id),
            [
                'tipo' => 'advertencia',
                'subtipo' => 'escrita',
                'titulo' => 'Ocorrência RH autorizada',
                'data_ocorrencia' => now()->toDateString(),
            ]
        );

        $response->assertRedirect(route('admin.system.equipe.ocorrencias.index', $this->employee1->id));
        $this->assertDatabaseHas('employee_occurrences', [
            'employee_id' => $this->employee1->id,
            'titulo' => 'Ocorrência RH autorizada',
            'created_by' => $usuarioRh->id,
        ]);
    }
}
