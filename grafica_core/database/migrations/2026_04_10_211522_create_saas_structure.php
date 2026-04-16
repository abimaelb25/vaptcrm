<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:17
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de Planos SaaS
        Schema::create('saas_planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->decimal('preco_mensal', 10, 2);
            $table->string('stripe_price_id')->nullable();
            
            // Limites (NULL = Ilimitado)
            $table->integer('limite_produtos')->nullable();
            $table->integer('limite_funcionarios')->nullable();
            
            // Recursos Adicionais (JSON)
            $table->json('recursos_premium')->nullable();
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela de Assinatura da Instância (Configuração Global do Tenant/Micro SaaS)
        Schema::create('saas_assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plano_id')->constrained('saas_planos');
            
            $table->string('status')->default('trial'); // trial, active, past_due, canceled, unpaid
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable();
            
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable(); // Data final da vigência paga
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_assinaturas');
        Schema::dropIfExists('saas_planos');
    }
};
