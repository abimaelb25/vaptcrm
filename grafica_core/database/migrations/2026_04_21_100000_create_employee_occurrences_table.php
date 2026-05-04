<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 10:00
| Descrição: Tabela de Ocorrências RH para registro estruturado e auditável de eventos funcionais.
*/

return new class extends Migration
{
    public function up(): void
    {
        // TABELA employee_occurrences
        // Centraliza ocorrências RH (advertências, suspensões, faltas, atestados, desligamentos)
        // com auditoria completa e suporte a multi-tenant.
        Schema::create('employee_occurrences', function (Blueprint $table) {
            $table->id();
            
            // Tenancy & Relacionamento
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // Tipificação
            $table->string('tipo'); // advertencia, suspensao, falta, atestado, desligamento
            $table->string('subtipo')->nullable(); // verbal/escrita, injustificada/justificada, pedido_demissao/sem_justa_causa/justa_causa, etc.
            
            // Identificação
            $table->string('titulo'); // ex: "Advertência Verbal", "Suspensão 3 dias", "Atestado Médico"
            $table->text('descricao')->nullable(); // motivo, contexto, observações
            
            // Datas
            $table->date('data_ocorrencia'); // data da ocorrência (não necessariamente data da criação)
            $table->date('data_inicio')->nullable(); // para suspensão, afastamento
            $table->date('data_fim')->nullable(); // para suspensão, afastamento, atestado
            
            // Status & Referência
            $table->string('status')->default('registrada'); // registrada, em_analise, resolvida, contestada, arquivada
            $table->string('referencia_documento')->nullable(); // ex: número de atestado, protocolo, processo
            
            // Metadados JSON (flexibilidade para dados específicos por tipo)
            // ex: {"dias_suspensao": 3, "motivo_verbal": "...", "assinado_pelo": "...", "arquivo_anexado": true}
            $table->json('metadados')->nullable();
            
            // Auditoria & Responsabilidade
            $table->foreignId('created_by')->constrained('usuarios')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('usuarios')->onDelete('restrict');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para performance (queries frequentes)
            $table->index(['loja_id', 'employee_id']); // listagem por colaborador
            $table->index(['loja_id', 'tipo']); // filtro por tipo de ocorrência
            $table->index(['loja_id', 'data_ocorrencia']); // ordenação cronológica
            $table->index(['employee_id', 'data_ocorrencia']); // histórico do colaborador
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_occurrences');
    }
};
