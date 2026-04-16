<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-07 22:32 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagamentos', function (Blueprint $table): void {
            // Distingue o contexto de cobrança
            $table->enum('tipo_cobranca', ['online', 'presencial'])->default('online')->after('pedido_id');

            // Identificadores do Stripe (indexados para lookup rápido nos webhooks)
            $table->string('stripe_session_id')->nullable()->unique()->after('tipo_cobranca')->index();
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id')->index();

            // URL do checkout Stripe — usada para QR Code presencial ou redirecionamento online
            $table->text('stripe_checkout_url')->nullable()->after('stripe_payment_intent_id');

            // Validade da sessão Stripe (Checkout Sessions expiram)
            $table->timestamp('stripe_expires_at')->nullable()->after('stripe_checkout_url');
        });

        // Atualiza o default do campo gateway para aceitar 'stripe'
        Schema::table('pagamentos', function (Blueprint $table): void {
            $table->string('gateway')->default('stripe')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table): void {
            $table->dropColumn([
                'tipo_cobranca',
                'stripe_session_id',
                'stripe_payment_intent_id',
                'stripe_checkout_url',
                'stripe_expires_at',
            ]);
            $table->string('gateway')->default('asaas')->change();
        });
    }
};
