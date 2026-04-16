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
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->enum('autor_tipo', ['cliente', 'suporte', 'interno']);
            $table->foreignId('autor_user_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('autor_master_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->text('mensagem');
            $table->string('anexo_path')->nullable();
            $table->boolean('interno')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
