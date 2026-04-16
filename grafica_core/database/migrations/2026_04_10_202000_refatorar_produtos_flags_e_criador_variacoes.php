<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Alteração da tabela base para comportar fluxos de venda separados
        Schema::table('produtos', function (Blueprint $table) {
            $table->enum('visibilidade', ['interno', 'publico', 'ambos'])->default('ambos')->after('ativo');
            $table->enum('tipo_precificacao', ['fixo', 'calculado'])->default('fixo')->after('preco_base');
        });

        // Criação do ecossistema de Combos / Adicionais do Produto
        Schema::create('produto_variacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->string('tipo_variacao', 80); // Categoria: ex 'Tamanho', 'Papel', 'Acabamento Opcional', 'Qtd'
            $table->string('nome_opcao', 150); // O valor exato: '5 Metros', 'Couche Premium', 'Verniz 3d'
            $table->decimal('acrescimo_venda', 10, 2)->default(0); // Acréscimo financeiro se ativado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_variacoes');

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['visibilidade', 'tipo_precificacao']);
        });
    }
};
