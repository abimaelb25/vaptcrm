<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:09
| Descrição: Rastreamento de notificações de inadimplência enviadas às lojas.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_notificacoes_inadimplencia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->nullable()->constrained('saas_assinaturas')->nullOnDelete();

            // Tipo da notificação no ciclo de inadimplência
            $table->enum('tipo', [
                'aviso_vencimento',      // 3 dias antes de vencer
                'vencida',               // no dia do vencimento
                'carencia',              // durante o período de carência
                'bloqueio',              // ao bloquear a conta
                'cancelamento',          // ao cancelar definitivamente
                'regularizacao',         // ao regularizar (confirmação)
            ])->index();

            // Canal de envio
            $table->enum('canal', ['email', 'sistema', 'whatsapp'])->default('email');

            // Conteúdo e rastreamento
            $table->text('mensagem')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->boolean('entregue')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_notificacoes_inadimplencia');
    }
};
