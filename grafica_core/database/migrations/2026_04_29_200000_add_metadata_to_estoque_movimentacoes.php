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
            if (!Schema::hasColumn('estoque_movimentacoes', 'metadata')) {
                $table->json('metadata')->nullable()->after('motivo');
            }
            if (!Schema::hasColumn('estoque_movimentacoes', 'quantidade_base')) {
                $table->decimal('quantidade_base', 15, 4)->nullable()->after('quantidade');
            }
            if (!Schema::hasColumn('estoque_movimentacoes', 'saldo_anterior')) {
                $table->decimal('saldo_anterior', 15, 4)->nullable()->after('valor_total');
            }
            if (!Schema::hasColumn('estoque_movimentacoes', 'saldo_posterior')) {
                $table->decimal('saldo_posterior', 15, 4)->nullable()->after('saldo_anterior');
            }
            if (!Schema::hasColumn('estoque_movimentacoes', 'origem_tela')) {
                $table->string('origem_tela', 50)->nullable()->after('origem');
            }
            if (!Schema::hasColumn('estoque_movimentacoes', 'motivo')) {
                $table->string('motivo', 100)->nullable()->after('descricao');
            }
        });

        // Mark the old migration as ran so it doesn't interfere
        DB::table('migrations')->updateOrInsert(
            ['migration' => '2026_04_28_150000_add_domain_fields_to_estoque_movimentacoes'],
            ['batch' => DB::table('migrations')->max('batch')]
        );
    }

    public function down(): void
    {
        Schema::table('estoque_movimentacoes', function (Blueprint $table) {
            $table->dropColumn(['metadata']);
        });
    }
};
