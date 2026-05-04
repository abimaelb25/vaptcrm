<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Adiciona vínculo opcional entre etapas e fases de produção.
|            FK usa SET NULL para preservar etapas caso fase seja excluída.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_steps', function (Blueprint $table) {
            $table->foreignId('production_phase_id')
                ->nullable()
                ->after('loja_id')
                ->constrained('production_phases')
                ->nullOnDelete();

            $table->index('production_phase_id');
        });
    }

    public function down(): void
    {
        Schema::table('production_steps', function (Blueprint $table) {
            $table->dropForeign(['production_phase_id']);
            $table->dropIndex(['production_phase_id']);
            $table->dropColumn('production_phase_id');
        });
    }
};
