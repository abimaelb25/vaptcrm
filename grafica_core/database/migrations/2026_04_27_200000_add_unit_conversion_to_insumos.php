<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autoria: Abimael Borges
     * Data: 2026-04-27
     * Descrição: Separação de unidade de compra vs unidade de consumo no cadastro de insumos.
     *
     * Backward compatible: todos os novos campos são nullable ou possuem default seguro.
     * Insumos existentes sem conversão continuam funcionando normalmente (quantidade_por_compra = 1).
     */
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            // Unidade na qual o material é comprado (ex: pacote, rolo, caixa, frasco, bobina).
            // NULL = mesma que unidade_medida (consumo direto, sem conversão).
            $table->string('unidade_compra')->nullable()->after('unidade_medida');

            // Quantas unidades de consumo existem em cada unidade de compra.
            // Exemplos: 500 (folhas por pacote), 60 (metros por rolo), 1000 (unidades por caixa).
            // Default 1 = sem conversão (compra e consumo são a mesma unidade).
            $table->decimal('quantidade_por_compra', 15, 4)->default(1)->after('unidade_compra');

            // Custo calculado por unidade de consumo. Atualizado automaticamente a cada entrada.
            // Fórmula: custo_compra / quantidade_por_compra.
            // Usado pela engine de precificação para custo real do material aplicado.
            $table->decimal('custo_unitario_consumo', 15, 6)->nullable()->after('ultimo_custo');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn(['unidade_compra', 'quantidade_por_compra', 'custo_unitario_consumo']);
        });
    }
};
