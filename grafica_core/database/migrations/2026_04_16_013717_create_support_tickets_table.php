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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('numero_ticket')->unique();
            $table->string('assunto');
            $table->foreignId('categoria_id')->nullable()->constrained('support_categories')->nullOnDelete();
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('status', ['aberto', 'aguardando_suporte', 'aguardando_cliente', 'resolvido', 'fechado'])->default('aberto');
            $table->foreignId('responsavel_master_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('ultimo_evento_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
