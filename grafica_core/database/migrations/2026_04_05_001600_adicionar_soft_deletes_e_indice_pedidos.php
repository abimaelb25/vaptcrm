<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SoftDeletes nos models principais
        Schema::table('clientes', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('produtos', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('pedidos', function (Blueprint $table): void {
            $table->softDeletes();
            // Índice composto para consulta pública por número + cliente
            $table->index(['numero', 'cliente_id'], 'pedidos_numero_cliente_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropIndex('pedidos_numero_cliente_idx');
            $table->dropSoftDeletes();
        });

        Schema::table('produtos', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
