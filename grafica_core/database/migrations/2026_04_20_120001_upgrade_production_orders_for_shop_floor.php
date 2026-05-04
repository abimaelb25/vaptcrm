<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('production_orders', 'cliente_nome')) {
                $table->string('cliente_nome')->nullable()->after('pedido_id');
            }

            if (!Schema::hasColumn('production_orders', 'produto_nome')) {
                $table->string('produto_nome')->nullable()->after('cliente_nome');
            }

            if (!Schema::hasColumn('production_orders', 'quantidade')) {
                $table->unsignedInteger('quantidade')->default(1)->after('produto_nome');
            }

            if (!Schema::hasColumn('production_orders', 'status_atual')) {
                $table->string('status_atual')->nullable()->after('quantidade');
            }

            if (!Schema::hasColumn('production_orders', 'production_step_id')) {
                $table->foreignId('production_step_id')
                    ->nullable()
                    ->after('status_atual')
                    ->constrained('production_steps')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('production_orders', 'data_finalizacao')) {
                $table->timestamp('data_finalizacao')->nullable()->after('data_previsao');
            }

            if (!Schema::hasColumn('production_orders', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('observacao');
            }
        });

        Schema::table('production_orders', function (Blueprint $table): void {
            $table->index('loja_id');
            $table->index('production_step_id');
            $table->index('status_atual');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE production_orders MODIFY prioridade ENUM('baixa','media','normal','alta','urgente') NOT NULL DEFAULT 'normal'");
            DB::table('production_orders')->where('prioridade', 'media')->update(['prioridade' => 'normal']);
            DB::statement("ALTER TABLE production_orders MODIFY prioridade ENUM('baixa','normal','alta','urgente') NOT NULL DEFAULT 'normal'");
        }

        DB::table('production_orders')
            ->whereNull('data_finalizacao')
            ->whereNotNull('data_conclusao')
            ->update(['data_finalizacao' => DB::raw('data_conclusao')]);

        DB::table('production_orders')
            ->whereNull('observacoes')
            ->whereNotNull('observacao')
            ->update(['observacoes' => DB::raw('observacao')]);

        DB::table('production_orders')
            ->whereNull('status_atual')
            ->update(['status_atual' => 'Sem etapa']);
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table): void {
            $table->dropIndex(['loja_id']);
            $table->dropIndex(['production_step_id']);
            $table->dropIndex(['status_atual']);

            if (Schema::hasColumn('production_orders', 'production_step_id')) {
                $table->dropForeign(['production_step_id']);
            }
        });

        Schema::table('production_orders', function (Blueprint $table): void {
            foreach (['cliente_nome', 'produto_nome', 'quantidade', 'status_atual', 'production_step_id', 'data_finalizacao', 'observacoes'] as $column) {
                if (Schema::hasColumn('production_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE production_orders MODIFY prioridade ENUM('baixa','media','alta','urgente') NOT NULL DEFAULT 'media'");
        }
    }
};
