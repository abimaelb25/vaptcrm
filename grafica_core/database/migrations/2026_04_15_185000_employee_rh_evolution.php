<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:50
*/

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABELA employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->onDelete('set null');
            
            $table->string('nome_completo');
            $table->string('nome_social')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('orgao_emissor', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('sexo', 20)->nullable();
            $table->string('estado_civil', 20)->nullable();
            $table->string('nacionalidade', 50)->nullable();
            $table->string('naturalidade', 50)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email_pessoal')->nullable();
            
            $table->string('cep', 10)->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('uf', 2)->nullable();

            $table->string('matricula', 50)->nullable();
            $table->string('cargo_formal')->nullable();
            $table->string('cargo_interno')->nullable();
            $table->string('setor')->nullable();
            $table->string('tipo_vinculo')->nullable(); 
            $table->date('data_admissao')->nullable();
            $table->date('data_desligamento')->nullable();
            $table->string('status_funcional')->default('ativo'); 

            $table->string('jornada_tipo')->nullable();
            $table->integer('carga_horaria_semanal')->nullable();
            $table->decimal('salario_base', 15, 2)->nullable();
            $table->decimal('comissao_percentual', 5, 2)->nullable();
            $table->text('observacoes_gerais')->nullable();

            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['loja_id', 'status_funcional']);
        });

        // 2. TABELA employee_documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('tipo_documento');
            $table->string('titulo');
            $table->string('arquivo_path');
            $table->string('mime_type')->nullable();
            $table->integer('tamanho_bytes')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        // 3. TABELA employee_vacations
        Schema::create('employee_vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('periodo_aquisitivo_inicio');
            $table->date('periodo_aquisitivo_fim');
            $table->date('periodo_concessivo_fim')->nullable();
            $table->integer('dias_direito')->default(30);
            $table->integer('dias_gozados')->default(0);
            $table->integer('dias_vendidos')->default(0);
            $table->integer('saldo_dias')->default(30);
            $table->date('inicio_gozo')->nullable();
            $table->date('fim_gozo')->nullable();
            $table->string('status')->default('em_aberto'); 
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        // 4. TABELA employee_health_records
        Schema::create('employee_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('tipo_registro');
            $table->date('data_registro');
            $table->date('validade_ate')->nullable();
            $table->text('observacao')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->timestamps();
        });

        // 5. TABELA employee_history
        Schema::create('employee_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('tipo_evento');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('data_evento');
            $table->foreignId('criado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_history');
        Schema::dropIfExists('employee_health_records');
        Schema::dropIfExists('employee_vacations');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employees');
    }
};
