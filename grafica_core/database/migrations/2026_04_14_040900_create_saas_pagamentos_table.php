<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:09
| Descrição: Histórico de cobranças SaaS por assinatura de loja.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_pagamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->constrained('saas_assinaturas')->cascadeOnDelete();

            // Identificadores Stripe
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable()->index();

            // Valores
            $table->decimal('valor', 10, 2);
            $table->string('moeda', 3)->default('BRL');

            // Status do pagamento
            $table->enum('status', [
                'pendente',
                'pago',
                'falhou',
                'reembolsado',
                'cancelado',
            ])->default('pendente')->index();

            // Período de referência da cobrança
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fim')->nullable();

            // Datas de controle
            $table->timestamp('pago_em')->nullable();
            $table->timestamp('vencimento_em')->nullable();

            // Contexto de falha (para diagnóstico)
            $table->string('motivo_falha')->nullable();
            $table->unsignedTinyInteger('tentativas')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_pagamentos');
    }
};
