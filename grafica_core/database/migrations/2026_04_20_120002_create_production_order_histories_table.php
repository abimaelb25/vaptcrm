<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('etapa_origem_id')->nullable()->constrained('production_steps')->nullOnDelete();
            $table->foreignId('etapa_destino_id')->constrained('production_steps')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('data_movimentacao');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('production_order_id');
            $table->index('etapa_destino_id');
            $table->index('data_movimentacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_histories');
    }
};
