<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Finaliza integração total do ERP de produção (vínculos de produto, itens e performance).
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('production_orders', 'produto_id')) {
                $table->foreignId('produto_id')->nullable()->after('pedido_id')->constrained('produtos')->nullOnDelete();
            }
            if (!Schema::hasColumn('production_orders', 'item_pedido_id')) {
                $table->foreignId('item_pedido_id')->nullable()->after('produto_id')->constrained('itens_pedido')->nullOnDelete();
            }
            if (!Schema::hasColumn('production_orders', 'valor_total')) {
                $table->decimal('valor_total', 15, 2)->nullable()->after('quantidade');
            }
        });

        // Adiciona colunas de auditoria e performance se faltarem
        Schema::table('production_order_steps', function (Blueprint $table): void {
            if (!Schema::hasColumn('production_order_steps', 'tempo_estimado')) {
                $table->unsignedInteger('tempo_estimado')->nullable()->after('data_fim');
            }
            if (!Schema::hasColumn('production_order_steps', 'tempo_real')) {
                $table->unsignedInteger('tempo_real')->nullable()->after('tempo_estimado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('produto_id');
            $table->dropConstrainedForeignId('item_pedido_id');
            $table->dropColumn('valor_total');
        });
    }
};
