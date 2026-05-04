<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autoria: Abimael Borges
 * Site: https://abimaelborges.adv.br
 * Data: 2026-04-28 11:30
 *
 * Corrige a precisão das colunas custo_medio e ultimo_custo de decimal(15,2)
 * para decimal(15,6). O InventoryService grava custo_medio em unidade de consumo
 * (ex: 1.46625 por folha), mas decimal(15,2) truncava para 1.47, causando
 * divergência entre preview e backend em cenários de conversão.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('custo_medio', 15, 6)->default(0)->change();
            $table->decimal('ultimo_custo', 15, 6)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('custo_medio', 15, 2)->default(0)->change();
            $table->decimal('ultimo_custo', 15, 2)->nullable()->change();
        });
    }
};
