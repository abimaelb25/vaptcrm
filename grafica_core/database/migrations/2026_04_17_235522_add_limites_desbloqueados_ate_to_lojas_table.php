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
        Schema::table('lojas', function (Blueprint $table) {
            $table->timestamp('limites_desbloqueados_ate')->nullable()->after('assinatura_ativa_ate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropColumn('limites_desbloqueados_ate');
        });
    }
};
