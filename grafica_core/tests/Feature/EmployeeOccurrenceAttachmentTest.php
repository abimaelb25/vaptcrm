<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeOccurrence;
use App\Models\EmployeeOccurrenceAttachment;
use App\Models\Loja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeOccurrenceAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private $loja;
    private $usuario;
    private $funcionario;
    private $ocorrencia;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Setup: Cria estrutura básica
        $this->loja = Loja::factory()->create();
        $this->usuario = Usuario::factory()->create(['loja_id' => $this->loja->id]);
        $this->funcionario = Employee::factory()->create(['loja_id' => $this->loja->id]);
        $this->ocorrencia = EmployeeOccurrence::factory()->create([
            'loja_id' => $this->loja->id,
            'employee_id' => $this->funcionario->id,
        ]);

        $this->actingAs($this->usuario);
    }

    /**
     * Teste 1: Upload de anexo com dados válidos
     */
    public function test_upload_anexo_valido()
    {
        $arquivo = UploadedFile::fake()->create('atestado.pdf', 100, 'application/pdf');

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.anexos.store', [
                'equipe' => $this->funcionario->id,
                'ocorrencia' => $this->ocorrencia->id,
            ]),
            [
                'arquivo' => $arquivo,
                'titulo' => 'Atestado Médico',
                'tipo_comprovacao' => 'atestado_medico',
                'descricao' => 'Atestado do dia 21/04/2026',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('sucesso');

        // Verifica se foi salvo no banco
        $this->assertDatabaseHas('employee_occurrence_attachments', [
            'employee_occurrence_id' => $this->ocorrencia->id,
            'titulo' => 'Atestado Médico',
            'tipo_comprovacao' => 'atestado_medico',
        ]);

        // Verifica arquivo no storage
        Storage::disk('public')->assertExists(
            "ocorrencias/{$this->loja->id}/{$this->ocorrencia->id}/*"
        );
    }

    /**
     * Teste 2: Rejeita arquivo com tipo MIME não permitido
     */
    public function test_upload_anexo_mime_invalido()
    {
        $arquivo = UploadedFile::fake()->create('documento.exe', 100);

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.anexos.store', [
                'equipe' => $this->funcionario->id,
                'ocorrencia' => $this->ocorrencia->id,
            ]),
            [
                'arquivo' => $arquivo,
                'titulo' => 'Arquivo suspeito',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('erro');

        // Não deve ter sido salvo
        $this->assertDatabaseMissing('employee_occurrence_attachments', [
            'titulo' => 'Arquivo suspeito',
        ]);
    }

    /**
     * Teste 3: Rejeita arquivo acima do tamanho máximo
     */
    public function test_upload_anexo_arquivo_muito_grande()
    {
        // Fake file de 11MB (máximo é 10MB)
        $arquivo = UploadedFile::fake()->create('grande.pdf', 11264);

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.anexos.store', [
                'equipe' => $this->funcionario->id,
                'ocorrencia' => $this->ocorrencia->id,
            ]),
            [
                'arquivo' => $arquivo,
                'titulo' => 'Arquivo grande',
            ]
        );

        // Laravel valida antes de chegar ao controller
        $response->assertSessionHasErrors('arquivo');
    }

    /**
     * Teste 4: Isolamento multi-tenant - não pode acessar anexo de outra loja
     */
    public function test_isolamento_multi_tenant()
    {
        // Cria segunda loja
        $loja2 = Loja::factory()->create();
        $usuario2 = Usuario::factory()->create(['loja_id' => $loja2->id]);
        $funcionario2 = Employee::factory()->create(['loja_id' => $loja2->id]);
        $ocorrencia2 = EmployeeOccurrence::factory()->create([
            'loja_id' => $loja2->id,
            'employee_id' => $funcionario2->id,
        ]);

        $anexo = EmployeeOccurrenceAttachment::factory()->create([
            'loja_id' => $this->loja->id,
            'employee_occurrence_id' => $this->ocorrencia->id,
        ]);

        // Usuário 2 tenta acessar anexo da loja 1
        $this->actingAs($usuario2);

        $response = $this->delete(
            route('admin.system.equipe.ocorrencias.anexos.destroy', [
                'equipe' => $funcionario2->id,
                'ocorrencia' => $ocorrencia2->id,
                'anexo' => $anexo->id,
            ])
        );

        $response->assertStatus(403);

        // Anexo não deve ter sido deletado
        $this->assertDatabaseHas('employee_occurrence_attachments', [
            'id' => $anexo->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Teste 5: Delete anexo com soft delete
     */
    public function test_delete_anexo()
    {
        $arquivo = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        // Cria anexo
        $this->post(
            route('admin.system.equipe.ocorrencias.anexos.store', [
                'equipe' => $this->funcionario->id,
                'ocorrencia' => $this->ocorrencia->id,
            ]),
            [
                'arquivo' => $arquivo,
                'titulo' => 'Documento para deletar',
            ]
        );

        $anexo = EmployeeOccurrenceAttachment::where('titulo', 'Documento para deletar')->first();

        // Deleta
        $response = $this->delete(
            route('admin.system.equipe.ocorrencias.anexos.destroy', [
                'equipe' => $this->funcionario->id,
                'ocorrencia' => $this->ocorrencia->id,
                'anexo' => $anexo->id,
            ])
        );

        $response->assertRedirect();
        $response->assertSessionHas('sucesso');

        // Verifica soft delete
        $this->assertDatabaseHas('employee_occurrence_attachments', [
            'id' => $anexo->id,
        ]);
        $this->assertSoftDeleted('employee_occurrence_attachments', [
            'id' => $anexo->id,
        ]);
    }

    /**
     * Teste 6: Desligamento com revogação de acesso
     */
    public function test_desligamento_com_revogacao_acesso()
    {
        // Associa user_id ao funcionário
        $this->funcionario->update(['user_id' => $this->usuario->id]);

        // Verifica que usuário está ativo
        $this->assertTrue($this->usuario->ativo);

        // Cria desligamento com revogar_acesso
        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->funcionario->id),
            [
                'tipo' => 'desligamento',
                'subtipo' => 'sem_justa_causa',
                'titulo' => 'Desligamento com revogação',
                'data_ocorrencia' => now()->toDateString(),
                'revogar_acesso' => true,
            ]
        );

        $response->assertRedirect();

        // Verifica que usuário foi desativado
        $this->assertFalse($this->usuario->fresh()->ativo);

        // Verifica que employee está com status desligado
        $this->assertEquals('desligado', $this->funcionario->fresh()->status_funcional);

        // Verifica registro no histórico
        $this->assertDatabaseHas('employee_history', [
            'employee_id' => $this->funcionario->id,
            'tipo_evento' => 'acesso_revogado',
        ]);
    }

    /**
     * Teste 7: Desligamento SEM revogação de acesso
     */
    public function test_desligamento_sem_revogacao_acesso()
    {
        $this->funcionario->update(['user_id' => $this->usuario->id]);

        $response = $this->post(
            route('admin.system.equipe.ocorrencias.store', $this->funcionario->id),
            [
                'tipo' => 'desligamento',
                'subtipo' => 'pedido_demissao',
                'titulo' => 'Desligamento sem revogação',
                'data_ocorrencia' => now()->toDateString(),
                'revogar_acesso' => false,
            ]
        );

        $response->assertRedirect();

        // Usuário AINDA está ativo
        $this->assertTrue($this->usuario->fresh()->ativo);

        // Employee está desligado
        $this->assertEquals('desligado', $this->funcionario->fresh()->status_funcional);
    }

    /**
     * Teste 8: Contagem de anexos na ocorrência
     */
    public function test_contagem_anexos()
    {
        // Cria 3 anexos
        EmployeeOccurrenceAttachment::factory()->count(3)->create([
            'employee_occurrence_id' => $this->ocorrencia->id,
            'loja_id' => $this->loja->id,
        ]);

        // Soft delete um
        $anexo = EmployeeOccurrenceAttachment::where('employee_occurrence_id', $this->ocorrencia->id)
            ->first();
        $anexo->delete();

        // Deve retornar 2 (excluindo o soft deletado)
        $contador = EmployeeOccurrenceAttachment::where('employee_occurrence_id', $this->ocorrencia->id)
            ->whereNull('deleted_at')
            ->count();

        $this->assertEquals(2, $contador);
    }

    /**
     * Teste 9: Helper method - getTamanhoFormatado
     */
    public function test_tamanho_formatado()
    {
        $anexo = EmployeeOccurrenceAttachment::factory()->create([
            'employee_occurrence_id' => $this->ocorrencia->id,
            'loja_id' => $this->loja->id,
            'tamanho_bytes' => 512000, // ~500KB
        ]);

        $this->assertStringContainsString('500', $anexo->getTamanhoFormatado());
    }
}
