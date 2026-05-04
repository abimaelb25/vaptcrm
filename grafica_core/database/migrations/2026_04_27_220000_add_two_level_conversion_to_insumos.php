<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona suporte a conversão em DOIS NÍVEIS de embalagem no cadastro de insumos.
 *
 * Exemplo: 1 caixa → 6 frascos → 100 ml cada
 *   unidade_compra                    = caixa (já existia)
 *   quantidade_subunidades_por_compra = 6
 *   unidade_subunidade                = frasco
 *   quantidade_consumo_por_subunidade = 100
 *   unidade_medida                    = ml    (já existia)
 *
 * Campos novos são NULLABLE → compatibilidade total com insumos existentes.
 * Quando null, o sistema continua usando o modelo de conversão simples (um nível).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('quantidade_subunidades_por_compra', 15, 4)->nullable()->after('quantidade_por_compra')
                ->comment('Quantas subunidades (ex: frascos) há dentro de cada unidade de compra (ex: caixa).');
            $table->string('unidade_subunidade', 50)->nullable()->after('quantidade_subunidades_por_compra')
                ->comment('Nome da subunidade intermediária (ex: frasco, ampola, sachê).');
            $table->decimal('quantidade_consumo_por_subunidade', 15, 4)->nullable()->after('unidade_subunidade')
                ->comment('Quantas unidades de consumo final (ex: ml) há dentro de cada subunidade (ex: frasco).');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn([
                'quantidade_subunidades_por_compra',
                'unidade_subunidade',
                'quantidade_consumo_por_subunidade',
            ]);
        });
    }
};
