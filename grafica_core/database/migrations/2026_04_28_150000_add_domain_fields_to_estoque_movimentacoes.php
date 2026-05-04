<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estoque_movimentacoes', function (Blueprint $table) {
            $table->decimal('quantidade_base', 15, 4)->nullable()->after('quantidade');
            $table->decimal('saldo_anterior', 15, 4)->nullable()->after('valor_total');
            $table->decimal('saldo_posterior', 15, 4)->nullable()->after('saldo_anterior');
            $table->string('origem_tela', 50)->nullable()->after('origem');
            $table->string('motivo', 100)->nullable()->after('descricao');
            $table->json('metadata')->nullable()->after('motivo');

            $table->index(['loja_id', 'insumo_id', 'data_movimentacao'], 'idx_mov_loja_insumo_data');
        });
    }

    public function down(): void
    {
        Schema::table('estoque_movimentacoes', function (Blueprint $table) {
            $table->dropIndex('idx_mov_loja_insumo_data');
            $table->dropColumn([
                'quantidade_base',
                'saldo_anterior',
                'saldo_posterior',
                'origem_tela',
                'motivo',
                'metadata',
            ]);
        });
    }
};
