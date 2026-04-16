<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 15/04/2026 14:30
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Evolução da tabela produtos
        Schema::table('produtos', function (Blueprint $table) {
            // Seção de Modo e Segmento
            if (!Schema::hasColumn('produtos', 'modelo_cadastro')) {
                $table->enum('modelo_cadastro', ['simples', 'configuravel', 'tecnico'])->default('simples')->after('visibilidade');
            }
            if (!Schema::hasColumn('produtos', 'segmento')) {
                $table->enum('segmento', ['grafica_rapida', 'comunicacao_visual', 'grafica_industrial'])->default('grafica_rapida')->after('modelo_cadastro');
            }

            // Seção de Marketing/Catálogo
            if (!Schema::hasColumn('produtos', 'subtitulo_comercial')) {
                $table->string('subtitulo_comercial', 150)->nullable()->after('nome');
                $table->string('frase_efeito', 100)->nullable()->after('descricao_curta');
                $table->string('badge_comercial', 50)->nullable()->after('frase_efeito'); // Ex: "Mais Vendido", "Oferta"
                $table->integer('ordem_exibicao')->default(0)->after('destaque');
            }

            // Seção de Venda e Preço
            if (!Schema::hasColumn('produtos', 'unidade_venda')) {
                $table->string('unidade_venda', 20)->default('unidade')->after('preco_base'); // unidade, m2, linear, pacote, etc
            }
            if (!Schema::hasColumn('produtos', 'oferece_design')) {
                $table->boolean('oferece_design')->default(false)->after('exige_arte');
                $table->decimal('custo_design', 10, 2)->default(0)->after('preco_arte');
            }

            // Seção de Especificações Técnicas
            if (!Schema::hasColumn('produtos', 'largura')) {
                $table->decimal('largura', 10, 3)->nullable()->after('categoria_id');
                $table->decimal('altura', 10, 3)->nullable()->after('largura');
                $table->decimal('area_m2', 12, 4)->nullable()->after('altura');
                $table->string('formato', 50)->nullable()->after('area_m2'); // Ex: "A4", "A3", "Personalizado"
                $table->enum('orientacao', ['vertical', 'horizontal', 'quadrado'])->nullable()->after('formato');
                $table->integer('gramatura')->nullable()->after('orientacao');
                $table->string('tipo_impressao', 50)->nullable()->after('gramatura');
                $table->string('cor_impressao', 20)->nullable()->after('tipo_impressao'); // Ex: "4x0", "4x4", "1x0"
            }

            // Seção de Produção
            if (!Schema::hasColumn('produtos', 'modo_producao')) {
                $table->enum('modo_producao', ['digital', 'offset', 'comunicacao_visual', 'terceirizado', 'outro'])->default('digital')->after('cor_impressao');
                $table->text('instrucoes_internas')->nullable()->after('descricao_completa');
                $table->text('checklist_producao')->nullable()->after('instrucoes_internas');
            }

            // Seção de Precificação Técnica
            if (!Schema::hasColumn('produtos', 'custo_base')) {
                $table->decimal('custo_base', 10, 2)->default(0)->after('preco_base');
                $table->decimal('custo_producao', 10, 2)->default(0)->after('custo_base');
                $table->decimal('margem_lucro', 10, 2)->default(0)->after('custo_producao'); // Em %
                $table->decimal('preco_sugerido', 10, 2)->default(0)->after('margem_lucro');
            }

            // Seção de SEO
            if (!Schema::hasColumn('produtos', 'meta_title')) {
                $table->string('meta_title', 70)->nullable()->after('ordem_exibicao');
                $table->string('meta_description', 160)->nullable()->after('meta_title');
            }
        });

        // 2. Criar tabelas auxiliares para nível técnico/configurável
        
        // Tabela de Materiais de Produto (Substratos)
        Schema::create('produto_materiais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->index(); // Multi-tenant
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->decimal('preco_ajuste', 10, 2)->default(0); 
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Tabela de Acabamentos de Produto
        Schema::create('produto_acabamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->index(); // Multi-tenant
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->decimal('preco_ajuste', 10, 2)->default(0); 
            $table->integer('prazo_ajuste')->default(0); // Em dias úteis
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Tabela de Faixas de Quantidade de Produto
        Schema::create('produto_faixas_quantidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->index(); // Multi-tenant
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->unsignedInteger('quantidade_minima');
            $table->decimal('preco_unitario', 12, 4)->default(0);
            $table->decimal('custo_unitario', 12, 4)->default(0);
            $table->timestamps();
        });

        // Nova Tabela de Grupos de Variação (Evolução técnica)
        Schema::create('produto_grupos_variacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->index(); // Multi-tenant
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('nome_grupo', 100); // Ex: "Tamanho", "Lado de Impressão"
            $table->string('tipo_exibicao')->default('select'); // select, radio, color, file
            $table->boolean('obrigatorio')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // Tabela de Opções de Variação vinculadas ao Grupo
        Schema::create('produto_opcoes_variacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('produto_grupos_variacao')->cascadeOnDelete();
            $table->string('nome_opcao', 150);
            $table->decimal('acrescimo_preco', 10, 2)->default(0);
            $table->decimal('acrescimo_custo', 10, 2)->default(0);
            $table->integer('acrescimo_prazo')->default(0);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_opcoes_variacao');
        Schema::dropIfExists('produto_grupos_variacao');
        Schema::dropIfExists('produto_faixas_quantidade');
        Schema::dropIfExists('produto_acabamentos');
        Schema::dropIfExists('produto_materiais');

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn([
                'modelo_cadastro', 'segmento', 'subtitulo_comercial', 'frase_efeito', 'badge_comercial', 'ordem_exibicao',
                'unidade_venda', 'oferece_design', 'custo_design', 'largura', 'altura', 'area_m2', 'formato', 
                'orientacao', 'gramatura', 'tipo_impressao', 'cor_impressao', 'modo_producao', 'instrucoes_internas', 
                'checklist_producao', 'custo_base', 'custo_producao', 'margem_lucro', 'preco_sugerido', 'meta_title', 'meta_description'
            ]);
        });
    }
};
