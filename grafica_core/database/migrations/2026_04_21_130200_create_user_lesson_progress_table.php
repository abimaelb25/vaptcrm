<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('help_content_id')->constrained('help_contents')->cascadeOnDelete();
            $table->unsignedTinyInteger('percentual_concluido')->default(0);
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->unique(['loja_id', 'user_id', 'help_content_id'], 'ul_progress_unique');
            $table->index(['loja_id', 'user_id']);
            $table->index(['help_content_id', 'percentual_concluido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};
