<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 20:51
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metricas_site', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            
            // Tipo: view, click, conversao
            $blueprint->string('tipo', 20)->index();
            
            // Entidade: produto, categoria, home, landing_page
            $blueprint->string('entidade_tipo', 30)->nullable()->index();
            $blueprint->unsignedBigInteger('entidade_id')->nullable()->index();
            
            // Metadados do Acesso
            $blueprint->string('origem')->nullable()->index(); // Referrer ou UTM
            $blueprint->string('dispositivo', 20)->nullable()->index(); // mobile, desktop
            $blueprint->string('navegador')->nullable();
            $blueprint->string('ip', 45)->nullable();
            
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metricas_site');
    }
};
