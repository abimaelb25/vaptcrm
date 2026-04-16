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
            // Atualiza o enum de origem para incluir 'pdv'
            // Nota: Usando default('interno') para manter compatibilidade
            $table->string('origem')->default('interno')->change(); 
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('tipo_atendimento')->default('presencial')->after('origem');
            $table->decimal('valor_recebido', 10, 2)->nullable()->after('total');
            $table->decimal('troco', 10, 2)->nullable()->after('valor_recebido');
            $table->foreignId('atendente_id')->nullable()->after('responsavel_id')->constrained('usuarios');
            $table->string('numero_acompanhamento')->nullable()->unique()->after('numero');
            $table->text('observacoes_internas')->nullable()->after('observacoes');
            $table->text('observacoes_cliente')->nullable()->after('observacoes_internas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['atendente_id']);
            $table->dropColumn([
                'tipo_atendimento',
                'valor_recebido',
                'troco',
                'atendente_id',
                'numero_acompanhamento',
                'observacoes_internas',
                'observacoes_cliente'
            ]);
        });
    }
};
