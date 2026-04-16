<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-13 13:20 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caixas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            $table->timestamp('data_abertura')->useCurrent()->index();
            $table->timestamp('data_fechamento')->nullable()->index();
            
            $table->decimal('valor_inicial', 15, 2)->default(0);
            $table->decimal('valor_vendas', 15, 2)->default(0); // Sugerido pelo sistema (soma das movimentações)
            $table->decimal('valor_fechamento', 15, 2)->nullable(); // Informado pelo atendente (sangria final)
            $table->decimal('diferenca', 15, 2)->nullable();
            
            $table->enum('status', ['aberto', 'fechado'])->default('aberto')->index();
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
