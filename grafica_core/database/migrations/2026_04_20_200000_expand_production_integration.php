<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Expande módulo de produção com insumos por etapa, equipamento vinculado e snapshot de OP.
*/

return new class extends Migration
{
    public function up(): void
    {
        // 1. Insumos vinculados à definição de etapa (BOM por etapa)
        Schema::create('production_step_insumos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('production_step_id')->constrained('production_steps')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('quantidade_por_unidade', 15, 4);
            $table->string('unidade_medida', 30)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->unique(['loja_id', 'production_step_id', 'insumo_id'], 'uniq_step_insumo');
            $table->index('insumo_id');
        });

        // 2. Equipamento vinculado à definição de etapa
        if (!Schema::hasColumn('production_steps', 'asset_id')) {
            Schema::table('production_steps', function (Blueprint $table): void {
                $table->foreignId('asset_id')->nullable()->after('ativo')
                    ->constrained('assets')->nullOnDelete();
            });
        }

        // 3. Tempo estimado padrão por etapa (definição)
        if (!Schema::hasColumn('production_steps', 'tempo_estimado_minutos')) {
            Schema::table('production_steps', function (Blueprint $table): void {
                $table->unsignedInteger('tempo_estimado_minutos')->nullable()->after('ativo');
            });
        }

        // 4. Snapshot de nome/ordem no production_order_steps (desacopla da definição)
        Schema::table('production_order_steps', function (Blueprint $table): void {
            if (!Schema::hasColumn('production_order_steps', 'nome_snapshot')) {
                $table->string('nome_snapshot')->nullable()->after('production_step_id');
            }

            if (!Schema::hasColumn('production_order_steps', 'ordem_snapshot')) {
                $table->unsignedInteger('ordem_snapshot')->nullable()->after('nome_snapshot');
            }

            if (!Schema::hasColumn('production_order_steps', 'fase_snapshot')) {
                $table->string('fase_snapshot')->nullable()->after('ordem_snapshot');
            }

            if (!Schema::hasColumn('production_order_steps', 'asset_id')) {
                $table->foreignId('asset_id')->nullable()->after('observacao')
                    ->constrained('assets')->nullOnDelete();
            }
        });

        // 5. Consumo real de insumos por etapa da OP
        Schema::create('production_order_step_insumos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('production_order_step_id')->constrained('production_order_steps')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('quantidade_prevista', 15, 4);
            $table->decimal('quantidade_consumida', 15, 4)->nullable();
            $table->boolean('baixa_estoque_realizada')->default(false);
            $table->timestamps();

            $table->index(['production_order_step_id', 'insumo_id'], 'idx_op_step_insumo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_step_insumos');

        Schema::table('production_order_steps', function (Blueprint $table): void {
            $columns = ['nome_snapshot', 'ordem_snapshot', 'fase_snapshot'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('production_order_steps', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('production_order_steps', 'asset_id')) {
                $table->dropConstrainedForeignId('asset_id');
            }
        });

        if (Schema::hasColumn('production_steps', 'tempo_estimado_minutos')) {
            Schema::table('production_steps', function (Blueprint $table): void {
                $table->dropColumn('tempo_estimado_minutos');
            });
        }

        if (Schema::hasColumn('production_steps', 'asset_id')) {
            Schema::table('production_steps', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('asset_id');
            });
        }

        Schema::dropIfExists('production_step_insumos');
    }
};
