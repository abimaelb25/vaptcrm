<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->indexExists('production_steps', 'idx_production_steps_phase_order')) {
            DB::statement('CREATE INDEX idx_production_steps_phase_order ON production_steps (production_phase_id, ordem)');
        }

        if (!$this->indexExists('production_orders', 'idx_production_orders_step_open')) {
            DB::statement('CREATE INDEX idx_production_orders_step_open ON production_orders (loja_id, production_step_id, data_finalizacao)');
        }

        if (!$this->indexExists('production_orders', 'idx_production_orders_priority_open')) {
            DB::statement('CREATE INDEX idx_production_orders_priority_open ON production_orders (loja_id, prioridade, data_finalizacao)');
        }

        if (!$this->indexExists('production_order_histories', 'idx_histories_order_movement')) {
            DB::statement('CREATE INDEX idx_histories_order_movement ON production_order_histories (production_order_id, data_movimentacao)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('production_order_histories', 'idx_histories_order_movement')) {
            DB::statement('DROP INDEX idx_histories_order_movement ON production_order_histories');
        }

        if ($this->indexExists('production_orders', 'idx_production_orders_priority_open')) {
            DB::statement('DROP INDEX idx_production_orders_priority_open ON production_orders');
        }

        if ($this->indexExists('production_orders', 'idx_production_orders_step_open')) {
            DB::statement('DROP INDEX idx_production_orders_step_open ON production_orders');
        }

        if ($this->indexExists('production_steps', 'idx_production_steps_phase_order')) {
            DB::statement('DROP INDEX idx_production_steps_phase_order ON production_steps');
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $result = DB::selectOne(
            'SELECT COUNT(1) as total FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
