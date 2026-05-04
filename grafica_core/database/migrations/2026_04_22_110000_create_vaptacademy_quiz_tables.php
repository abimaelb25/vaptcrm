<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('help_content_id')->constrained('help_contents')->cascadeOnDelete();
                $table->text('pergunta');
                $table->unsignedInteger('ordem')->default(1);
                $table->timestamps();

                $table->index(['help_content_id', 'ordem']);
            });
        }

        if (! Schema::hasTable('quiz_answers')) {
            Schema::create('quiz_answers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->text('texto');
                $table->boolean('is_correct')->default(false);
                $table->unsignedInteger('ordem')->default(1);
                $table->timestamps();

                $table->index(['question_id', 'ordem']);
            });
        }

        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('loja_id')->index();
                $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
                $table->foreignId('help_content_id')->constrained('help_contents')->cascadeOnDelete();
                $table->unsignedTinyInteger('tentativa')->default(1);
                $table->unsignedInteger('nota')->default(0);
                $table->unsignedInteger('percentual_acerto')->default(0);
                $table->unsignedInteger('percentual_erro')->default(0);
                $table->unsignedInteger('acertos')->default(0);
                $table->unsignedInteger('erros')->default(0);
                $table->unsignedInteger('total_questoes')->default(0);
                $table->boolean('finalizada')->default(false);
                $table->timestamp('iniciada_em')->nullable();
                $table->timestamp('finalizada_em')->nullable();
                $table->unsignedInteger('duracao_segundos')->nullable();
                $table->timestamps();

                $table->unique(['loja_id', 'user_id', 'help_content_id', 'tentativa'], 'quiz_attempt_unique_per_try');
                $table->index(['loja_id', 'help_content_id', 'finalizada']);
            });
        }

        if (! Schema::hasTable('quiz_attempt_answers')) {
            Schema::create('quiz_attempt_answers', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('loja_id')->index();
                $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->foreignId('answer_id')->constrained('quiz_answers')->cascadeOnDelete();
                $table->boolean('correto')->default(false);
                $table->timestamp('respondido_em')->nullable();
                $table->timestamps();

                $table->unique(['attempt_id', 'question_id'], 'quiz_attempt_one_answer_per_question');
                $table->index(['loja_id', 'question_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_questions');
    }
};
