<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autoria: Abimael Borges
     * Site: https://abimaelborges.adv.br
     * Data: 2026-04-15 18:00
     * Descrição: Estrutura profissional para o Sistema Financeiro Operacional (Vapt Finance PRO).
     */
    public function up(): void
    {
        // 1. Categorias Financeiras (DRE / Fluxo)
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('cor', 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Contas Financeiras (Caixa, Bancos, etc)
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->string('nome');
            $table->string('tipo')->default('caixa'); // caixa, banco, digital, etc
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Títulos Financeiros (A Pagar / A Receber)
        Schema::create('financial_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->enum('tipo', ['receber', 'pagar']);
            $table->string('origem')->default('manual'); // pedido, manual, despesa, ajuste
            $table->unsignedBigInteger('referencia_id')->nullable(); // Ex: pedido_id
            $table->string('descricao');
            $table->foreignId('categoria_id')->nullable()->constrained('financial_categories')->onDelete('set null');
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->decimal('saldo_restante', 15, 2);
            $table->date('data_emissao');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['aberto', 'parcial', 'pago', 'vencido', 'cancelado'])->default('aberto');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['loja_id', 'tipo', 'status']);
            $table->index(['referencia_id', 'origem']);
        });

        // 4. Pagamentos Realizados (Vinculados aos Títulos)
        Schema::create('financial_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->onDelete('cascade');
            $table->foreignId('financial_title_id')->constrained('financial_titles')->onDelete('cascade');
            $table->foreignId('financial_account_id')->nullable()->constrained('financial_accounts')->onDelete('set null');
            $table->decimal('valor', 15, 2);
            $table->string('forma_pagamento'); // pix, dinheiro, cartao, etc
            $table->date('data_pagamento');
            $table->string('comprovante_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_payments');
        Schema::dropIfExists('financial_titles');
        Schema::dropIfExists('financial_accounts');
        Schema::dropIfExists('financial_categories');
    }
};
