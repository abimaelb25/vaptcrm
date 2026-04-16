<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autoria: Abimael Borges
     * Site: https://abimaelborges.adv.br
     * Data: 2026-04-15 18:15
     * Descrição: Módulo de Produção Gráfica e Gestão de Ativos/Equipamentos.
     */
    public function up(): void
    {
        // 1. Etapas de Produção (Definições)
        Schema::create('production_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Equipamentos / Ativos
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->string('tipo'); // impressora, plotter, corte, etc.
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->string('setor')->nullable();
            $table->date('data_aquisicao');
            $table->decimal('valor_aquisicao', 15, 2);
            $table->integer('vida_util_meses');
            $table->decimal('valor_residual', 15, 2)->nullable()->default(0);
            $table->enum('status', ['ativo', 'manutencao', 'inativo'])->default('ativo');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Ordens de Produção (Vínculo com Pedido)
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->enum('status', ['aguardando', 'em_producao', 'pausado', 'finalizado'])->default('aguardando');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->timestamp('data_inicio')->nullable();
            $table->timestamp('data_previsao')->nullable();
            $table->timestamp('data_conclusao')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['loja_id', 'status']);
        });

        // 4. Etapas da Execução (Instâncias por OP)
        Schema::create('production_order_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('production_order_id')->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('production_step_id')->constrained('production_steps')->onDelete('cascade');
            $table->enum('status', ['pendente', 'em_andamento', 'concluido'])->default('pendente');
            $table->foreignId('responsavel_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('data_inicio')->nullable();
            $table->timestamp('data_fim')->nullable();
            $table->integer('tempo_estimado')->nullable(); // em minutos
            $table->integer('tempo_real')->nullable(); // em minutos
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        // 5. Manutenções de Ativos
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('tipo', ['preventiva', 'corretiva']);
            $table->date('data');
            $table->decimal('custo', 15, 2);
            $table->text('descricao');
            $table->foreignId('responsavel_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('production_order_steps');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('production_steps');
    }
};
