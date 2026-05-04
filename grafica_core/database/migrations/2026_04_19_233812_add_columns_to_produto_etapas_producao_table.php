<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produto_etapas_producao', function (Blueprint $table) {
            $table->foreignId('loja_id')->after('id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('produto_id')->after('loja_id')->constrained('produtos')->cascadeOnDelete();
            $table->foreignId('production_step_id')->after('produto_id')->constrained('production_steps')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem')->default(0)->after('production_step_id');
            $table->unsignedSmallInteger('tempo_estimado_minutos')->nullable()->after('ordem');
            $table->boolean('obrigatorio')->default(true)->after('tempo_estimado_minutos');

            $table->unique(['loja_id', 'produto_id', 'production_step_id'], 'produto_etapa_unique');
            $table->index(['loja_id', 'produto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produto_etapas_producao', function (Blueprint $table) {
            $table->dropUnique('produto_etapa_unique');
            $table->dropIndex(['loja_id', 'produto_id']);
            $table->dropForeign(['loja_id']);
            $table->dropForeign(['produto_id']);
            $table->dropForeign(['production_step_id']);
            $table->dropColumn(['loja_id', 'produto_id', 'production_step_id', 'ordem', 'tempo_estimado_minutos', 'obrigatorio']);
        });
    }
};
