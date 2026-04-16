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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('tipo_pessoa', 1)->default('F')->after('empresa')->comment('F = Física, J = Jurídica');
            $table->string('avatar')->nullable()->after('tipo_pessoa');
            $table->date('data_nascimento')->nullable()->after('cidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['tipo_pessoa', 'avatar', 'data_nascimento']);
        });
    }
};
