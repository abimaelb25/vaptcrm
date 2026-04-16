<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-11 01:28 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracoes_pagamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('gateway', 40);
            $table->boolean('ativo')->default(false);
            $table->string('ambiente', 20)->default('sandbox');
            $table->text('credenciais')->nullable();
            $table->json('config_json')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'gateway']);
            $table->index(['user_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracoes_pagamento');
    }
};
