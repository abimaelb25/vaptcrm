<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Configuração Global da Loja
        Schema::create('loja_precificacao_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->boolean('precificacao_dinamica_ativa')->default(false); // Feature Flag de Rollout
            $table->decimal('custo_fixo_mensal', 12, 2)->default(0);
            $table->integer('horas_produtivas_mensais')->default(220);
            $table->decimal('comissao_percentual', 5, 2)->default(0);
            $table->decimal('imposto_percentual', 5, 2)->default(0);
            $table->decimal('taxas_percentual', 5, 2)->default(0);
            $table->json('margens_padrao')->nullable(); 
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique('loja_id');
        });

        // 2. Serviços de Produção
        Schema::create('servicos_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->string('nome');
            $table->enum('tipo_cobranca', ['hora', 'unidade', 'metro_quadrado']);
            $table->decimal('custo_base', 10, 4)->default(0);
            $table->integer('tempo_medio_min')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['loja_id', 'ativo']);
        });

        // 3. Ficha Técnica do Produto
        Schema::create('produto_fichas_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('nome')->default('Composição Padrão');
            $table->integer('quantidade_base')->default(1);
            $table->integer('tempo_producao_min')->default(0);
            $table->decimal('perda_percentual', 5, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique(['loja_id', 'produto_id']);
        });

        // 4. Insumos da Ficha Técnica
        Schema::create('produto_ficha_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('ficha_tecnica_id')->constrained('produto_fichas_tecnicas')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos');
            $table->decimal('quantidade', 12, 4);
            $table->decimal('fator_perda', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index('ficha_tecnica_id');
            $table->index('insumo_id');
        });

        // 5. Serviços da Ficha Técnica
        Schema::create('produto_ficha_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('ficha_tecnica_id')->constrained('produto_fichas_tecnicas')->cascadeOnDelete();
            $table->foreignId('servico_producao_id')->constrained('servicos_producao');
            $table->decimal('quantidade', 12, 4); 
            $table->decimal('fator_aplicacao', 5, 2)->default(1.0);
            $table->timestamps();
            
            $table->index('ficha_tecnica_id');
        });

        // 6. Histórico de Preços (Auditoria Detalhada)
        Schema::create('produto_historico_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('modo_precificacao', 50)->default('manual'); // 'manual', 'dinamico', 'hibrido'
            $table->decimal('custo_base', 12, 4)->nullable();
            $table->decimal('preco_equilibrio', 12, 4)->nullable();
            $table->decimal('preco_sugerido', 12, 4)->nullable();
            $table->decimal('preco_manual_vigente', 12, 4)->nullable();
            $table->string('origem_recalculo'); 
            $table->foreignId('usuario_responsavel_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['loja_id', 'produto_id']);
        });

        // Modificações de Material e Acabamento para suportar custos (Para suportar "Custos do Produto")
        Schema::table('produto_materiais', function (Blueprint $table) {
            $table->decimal('custo_ajuste', 10, 4)->default(0)->after('nome');
        });
        
        Schema::table('produto_acabamentos', function (Blueprint $table) {
            $table->decimal('custo_ajuste', 10, 4)->default(0)->after('nome');
            $table->decimal('tempo_producao_adicional_min', 8, 2)->default(0)->after('prazo_ajuste');
        });
    }

    public function down(): void
    {
        Schema::table('produto_acabamentos', function (Blueprint $table) {
            $table->dropColumn(['custo_ajuste', 'tempo_producao_adicional_min']);
        });
        
        Schema::table('produto_materiais', function (Blueprint $table) {
            $table->dropColumn('custo_ajuste');
        });

        Schema::dropIfExists('produto_historico_precos');
        Schema::dropIfExists('produto_ficha_servicos');
        Schema::dropIfExists('produto_ficha_insumos');
        Schema::dropIfExists('produto_fichas_tecnicas');
        Schema::dropIfExists('servicos_producao');
        Schema::dropIfExists('loja_precificacao_configs');
    }
};
