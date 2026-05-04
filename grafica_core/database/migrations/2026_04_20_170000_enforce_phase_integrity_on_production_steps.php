<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $phaseDefinitions = [
            ['nome' => 'Pré-produção', 'ordem' => 1],
            ['nome' => 'Produção', 'ordem' => 2],
            ['nome' => 'Pós-produção', 'ordem' => 3],
            ['nome' => 'Finalização', 'ordem' => 4],
        ];

        $fixedStepMap = [
            'Pré-produção' => ['Tratamento do Arquivo', 'Tratamento de Arquivos'],
            'Produção' => ['Impressão', 'Impressao', 'Impressão EC-C7000', 'Impressao EC-C7000'],
            'Pós-produção' => ['Corte', 'Laminação', 'Laminacao', 'Picote', 'Grampo'],
            'Finalização' => ['Embalagem'],
        ];

        $lojaIds = DB::table('production_steps')
            ->select('loja_id')
            ->distinct()
            ->pluck('loja_id');

        foreach ($lojaIds as $lojaId) {
            $phaseIdsByName = [];

            foreach ($phaseDefinitions as $phaseDefinition) {
                $existingId = DB::table('production_phases')
                    ->where('loja_id', $lojaId)
                    ->where('nome', $phaseDefinition['nome'])
                    ->value('id');

                if ($existingId === null) {
                    $existingId = DB::table('production_phases')->insertGetId([
                        'loja_id' => $lojaId,
                        'nome' => $phaseDefinition['nome'],
                        'ordem' => $phaseDefinition['ordem'],
                        'ativo' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $phaseIdsByName[$phaseDefinition['nome']] = (int) $existingId;
            }

            foreach ($fixedStepMap as $phaseName => $stepNames) {
                $phaseId = $phaseIdsByName[$phaseName] ?? null;

                if ($phaseId === null) {
                    continue;
                }

                foreach ($stepNames as $stepName) {
                    DB::table('production_steps')
                        ->where('loja_id', $lojaId)
                        ->whereRaw('LOWER(nome) = ?', [mb_strtolower($stepName)])
                        ->update(['production_phase_id' => $phaseId]);
                }
            }

            $defaultPhaseId = $phaseIdsByName['Pré-produção'] ?? null;

            if ($defaultPhaseId !== null) {
                DB::table('production_steps')
                    ->where('loja_id', $lojaId)
                    ->whereNull('production_phase_id')
                    ->update(['production_phase_id' => $defaultPhaseId]);
            }

            $phaseIds = DB::table('production_steps')
                ->where('loja_id', $lojaId)
                ->whereNotNull('production_phase_id')
                ->select('production_phase_id')
                ->distinct()
                ->pluck('production_phase_id');

            foreach ($phaseIds as $phaseId) {
                $stepIds = DB::table('production_steps')
                    ->where('loja_id', $lojaId)
                    ->where('production_phase_id', $phaseId)
                    ->orderBy('ordem')
                    ->orderBy('id')
                    ->pluck('id');

                foreach ($stepIds as $index => $stepId) {
                    DB::table('production_steps')
                        ->where('id', $stepId)
                        ->update(['ordem' => $index + 1]);
                }
            }

            $productionPhaseId = $phaseIdsByName['Produção'] ?? null;

            if ($productionPhaseId !== null) {
                $productionStepsCount = DB::table('production_steps')
                    ->where('loja_id', $lojaId)
                    ->where('production_phase_id', $productionPhaseId)
                    ->where('ativo', 1)
                    ->whereNull('deleted_at')
                    ->count();

                if ($productionStepsCount === 0) {
                    DB::table('production_steps')->insert([
                        'loja_id' => $lojaId,
                        'production_phase_id' => $productionPhaseId,
                        'nome' => 'Impressão EC-C7000',
                        'ordem' => 1,
                        'ativo' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (!$this->indexExists('production_steps', 'uniq_production_steps_phase_order')) {
            Schema::table('production_steps', function (Blueprint $table) {
                $table->unique(['loja_id', 'production_phase_id', 'ordem'], 'uniq_production_steps_phase_order');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            Schema::table('production_steps', function (Blueprint $table) {
                $table->dropForeign(['production_phase_id']);
            });

            DB::statement('ALTER TABLE production_steps MODIFY production_phase_id BIGINT UNSIGNED NOT NULL');

            Schema::table('production_steps', function (Blueprint $table) {
                $table->foreign('production_phase_id')
                    ->references('id')
                    ->on('production_phases')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('production_steps', 'uniq_production_steps_phase_order')) {
            Schema::table('production_steps', function (Blueprint $table) {
                $table->dropUnique('uniq_production_steps_phase_order');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            Schema::table('production_steps', function (Blueprint $table) {
                $table->dropForeign(['production_phase_id']);
            });

            DB::statement('ALTER TABLE production_steps MODIFY production_phase_id BIGINT UNSIGNED NULL');

            Schema::table('production_steps', function (Blueprint $table) {
                $table->foreign('production_phase_id')
                    ->references('id')
                    ->on('production_phases')
                    ->nullOnDelete();
            });
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

        if (DB::getDriverName() === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

            return !empty($rows);
        }

        return false;
    }
};
