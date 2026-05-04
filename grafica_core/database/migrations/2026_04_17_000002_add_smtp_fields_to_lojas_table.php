<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Adiciona campos de configuração SMTP por loja (multi-tenant mail).
*/

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->string('smtp_host')->nullable()->after('dominio_personalizado');
            $table->unsignedSmallInteger('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption', 10)->nullable()->after('smtp_password'); // tls, ssl, null
            $table->string('smtp_from_address')->nullable()->after('smtp_encryption');
            $table->string('smtp_from_name')->nullable()->after('smtp_from_address');
        });
    }

    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'smtp_from_address',
                'smtp_from_name',
            ]);
        });
    }
};
