<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autoria: Abimael Borges
     * Site: https://abimaelborges.adv.br
     * Data: 2026-04-15 18:10
     * Descrição: Módulo de Estoque de Insumos para Gráficas (Matéria-prima).
     */
    public function up(): void
    {
        // 1. Fornecedores
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->string('razao_social')->nullable();
            $table->string('cnpj_cpf')->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->text('observacao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Insumos (Matéria-prima)
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->string('codigo_interno')->nullable();
            $table->string('categoria')->nullable(); // papel, tinta, lona, adesivo, acabamento, etc.
            $table->string('unidade_medida'); // unidade, folha, metro, m2, litro, kg, bobina
            $table->decimal('estoque_atual', 15, 4)->default(0);
            $table->decimal('estoque_minimo', 15, 4)->default(0);
            $table->decimal('estoque_maximo', 15, 4)->nullable();
            $table->decimal('custo_medio', 15, 2)->default(0);
            $table->decimal('ultimo_custo', 15, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['loja_id', 'categoria']);
            $table->index(['loja_id', 'ativo']);
        });

        // 3. Movimentações de Estoque
        Schema::create('estoque_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'saida', 'ajuste']);
            $table->enum('origem', ['compra', 'manual', 'producao', 'perda', 'ajuste']);
            $table->decimal('quantidade', 15, 4);
            $table->decimal('custo_unitario', 15, 2)->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
            $table->unsignedBigInteger('referencia_id')->nullable(); // Vínculo futuro com pedidos/produção
            $table->string('descricao')->nullable();
            $table->timestamp('data_movimentacao');
            $table->foreignId('usuario_id')->constrained('usuarios'); // Quem realizou a ação
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentacoes');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('fornecedores');
    }
};
