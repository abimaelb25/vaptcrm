<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:35 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_financeiras', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->enum('tipo', ['entrada', 'saida'])->index();
            $table->string('categoria')->index(); // venda, despesa_fixa, despesa_variavel, compra_insumo, pro_labore, etc
            
            $table->decimal('valor', 15, 2);
            $table->date('data_movimentacao')->index();
            $table->string('forma_pagamento')->nullable()->index(); // pix, cartao_credito, cartao_debito, dinheiro, boleto
            
            $table->enum('status', ['pendente', 'pago', 'cancelado'])->default('pago')->index();
            
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->foreignId('pagamento_id')->nullable()->constrained('pagamentos')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            
            $table->text('descricao')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_financeiras');
    }
};
