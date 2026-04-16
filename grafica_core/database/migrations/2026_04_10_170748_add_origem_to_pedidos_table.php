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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('origem', ['interno', 'online', 'orcamento_convertido'])->default('interno')->after('numero');
        });

        // Transmutar os pedidos que estão com status antigo de 'orcamento' para a base na nova esteira de 'rascunho'
        \Illuminate\Support\Facades\DB::table('pedidos')
            ->where('status', 'orcamento')
            ->update(['status' => 'rascunho']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('origem');
        });

        \Illuminate\Support\Facades\DB::table('pedidos')
            ->where('status', 'rascunho')
            ->update(['status' => 'orcamento']);
    }
};
