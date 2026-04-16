<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:09
| Descrição: Adiciona campos de bloqueio por inadimplência à tabela lojas.
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->timestamp('bloqueada_em')->nullable()->after('status');
            $table->string('motivo_bloqueio')->nullable()->after('bloqueada_em');
            $table->unsignedTinyInteger('dias_carencia')->default(3)->after('motivo_bloqueio');
            $table->timestamp('ultima_notificacao_em')->nullable()->after('dias_carencia');
        });
    }

    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropColumn(['bloqueada_em', 'motivo_bloqueio', 'dias_carencia', 'ultima_notificacao_em']);
        });
    }
};
