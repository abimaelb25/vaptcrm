<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->enum('tipo_item_operacional', ['consumivel', 'embalagem', 'componente', 'apoio', 'ignorado'])
                ->default('consumivel')
                ->after('categoria');
            $table->boolean('controlar_estoque')->default(true)->after('quantidade_por_compra');
            $table->boolean('usar_na_precificacao')->default(true)->after('controlar_estoque');

            $table->index(['loja_id', 'tipo_item_operacional'], 'idx_insumos_loja_tipo_operacional');
            $table->index(['loja_id', 'usar_na_precificacao'], 'idx_insumos_loja_uso_precificacao');
        });

        Schema::table('documentos_fiscais_entrada_itens', function (Blueprint $table) {
            $table->enum('tipo_item_operacional', ['consumivel', 'embalagem', 'componente', 'apoio', 'ignorado'])
                ->nullable()
                ->after('acao_definida');

            $table->enum('tratamento_financeiro', ['custo_proprio', 'ratear_consumiveis', 'custo_agregado', 'desconsiderar'])
                ->default('custo_proprio')
                ->after('tipo_item_operacional');

            $table->decimal('valor_financeiro_alocado', 15, 2)->nullable()->after('tratamento_financeiro');
            $table->boolean('confirmacao_desconsideracao')->default(false)->after('valor_financeiro_alocado');

            $table->index(['loja_id', 'tipo_item_operacional'], 'idx_doc_itens_loja_tipo_operacional');
            $table->index(['loja_id', 'tratamento_financeiro'], 'idx_doc_itens_loja_tratamento_financeiro');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_fiscais_entrada_itens', function (Blueprint $table) {
            $table->dropIndex('idx_doc_itens_loja_tipo_operacional');
            $table->dropIndex('idx_doc_itens_loja_tratamento_financeiro');
            $table->dropColumn([
                'tipo_item_operacional',
                'tratamento_financeiro',
                'valor_financeiro_alocado',
                'confirmacao_desconsideracao',
            ]);
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropIndex('idx_insumos_loja_tipo_operacional');
            $table->dropIndex('idx_insumos_loja_uso_precificacao');
            $table->dropColumn([
                'tipo_item_operacional',
                'controlar_estoque',
                'usar_na_precificacao',
            ]);
        });
    }
};
