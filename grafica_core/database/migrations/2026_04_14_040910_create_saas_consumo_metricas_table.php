<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:09
| Descrição: Snapshots mensais de consumo de recursos por loja.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_consumo_metricas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();

            // Período do snapshot
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');

            // Consumo atual no período
            $table->unsignedInteger('total_produtos')->default(0);
            $table->unsignedInteger('total_usuarios')->default(0);
            $table->unsignedInteger('total_pedidos')->default(0);
            $table->unsignedBigInteger('storage_bytes')->default(0);

            // Snapshot dos limites do plano no momento do registro
            $table->unsignedInteger('limite_produtos')->nullable();
            $table->unsignedInteger('limite_usuarios')->nullable();
            $table->unsignedBigInteger('limite_storage_bytes')->nullable();

            // Evita snapshots duplicados para o mesmo mês/ano por loja
            $table->unique(['loja_id', 'mes', 'ano'], 'consumo_loja_periodo_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_consumo_metricas');
    }
};
