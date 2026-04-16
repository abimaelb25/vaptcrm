<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:10 -03:00
*/

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
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('solicitante_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('status')->default('backlog'); // backlog, a_fazer, em_andamento, bloqueada, concluida, cancelada
            $table->string('prioridade')->default('media'); // baixa, media, alta, urgente
            $table->datetime('prazo')->nullable();
            $table->string('setor')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('prioridade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
