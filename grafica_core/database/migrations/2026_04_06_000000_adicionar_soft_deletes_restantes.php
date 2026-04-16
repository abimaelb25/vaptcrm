<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 00:00 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('contatos', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('itens_pedido', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('pagamentos', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('itens_pedido', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('contatos', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
