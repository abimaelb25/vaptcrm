<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 21:02
*/

return new class extends Migration
{
    public function up(): void
    {
        $tabelas = ['pedidos', 'produtos', 'clientes'];

        foreach ($tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'created_by_id')) {
                    $table->foreignId('created_by_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
                if (!Schema::hasColumn($table->getTable(), 'updated_by_id')) {
                    $table->foreignId('updated_by_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        $tabelas = ['pedidos', 'produtos', 'clientes'];

        foreach ($tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['created_by_id']);
                $table->dropForeign(['updated_by_id']);
                $table->dropColumn(['created_by_id', 'updated_by_id']);
            });
        }
    }
};
