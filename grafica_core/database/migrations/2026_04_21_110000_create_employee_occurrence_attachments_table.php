<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-21 11:00
| Descrição: Tabela de Anexos para Ocorrências RH
*/

return new class extends Migration
{
    public function up(): void
    {
        // TABELA employee_occurrence_attachments
        // Armazena anexos comprobatórios de ocorrências (atestados, comprovantes, etc)
        Schema::create('employee_occurrence_attachments', function (Blueprint $table) {
            $table->id();
            
            // Tenancy & Relacionamento
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_occurrence_id')->constrained('employee_occurrences')->onDelete('cascade');
            
            // Arquivo
            $table->string('titulo'); // ex: "Atestado Médico"
            $table->string('arquivo_path'); // path relativo no storage
            $table->string('mime_type'); // ex: application/pdf
            $table->integer('tamanho_bytes'); // para tracking de quota
            
            // Metadados opcionais
            $table->string('tipo_comprovacao')->nullable(); // ex: atestado_medico, recibo, etc
            $table->text('descricao')->nullable(); // por que este anexo foi adicionado
            
            // Auditoria
            $table->foreignId('uploaded_by')->constrained('usuarios')->onDelete('restrict');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para performance
            $table->index(['loja_id', 'employee_occurrence_id']); // listagem por ocorrência
            $table->index(['employee_occurrence_id']); // ocorrências com anexos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_occurrence_attachments');
    }
};
