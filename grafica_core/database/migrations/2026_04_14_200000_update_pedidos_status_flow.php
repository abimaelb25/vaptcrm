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
        // Como o status é uma string no Laravel (conforme migrações anteriores), 
        // apenas garantimos que o código suporte novos valores.
        // Se fosse ENUM, precisaríamos de uma query DB::statement.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
