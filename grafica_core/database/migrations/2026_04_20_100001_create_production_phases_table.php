<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-20
| Descrição: Cria tabela de FASES de produção (agrupamento macro de etapas).
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['loja_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_phases');
    }
};
