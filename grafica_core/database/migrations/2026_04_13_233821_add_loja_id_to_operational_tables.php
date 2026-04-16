<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 00:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas que receberão a coluna loja_id
     */
    private array $tables = [
        'usuarios',
        'clientes',
        'produtos',
        'categorias',
        'pedidos',
        'itens_pedido',
        'contatos',
        'pagamentos',
        'historicos_pedido',
        'auditorias',
        'tarefas',
        'site_configuracoes',
        'banners',
        'depoimentos',
        'paginas_legais',
        'metricas_site',
        'cupons',
        'integracoes_pagamento',
        'movimentacoes_financeiras',
        'caixas',
        'saas_assinaturas'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'loja_id')) {
                        $table->foreignId('loja_id')->after('id')->nullable()->constrained('lojas')->cascadeOnDelete();
                    }
                });
            }
        }

        // Ajustar índices únicos para considerar loja_id
        
        // SITE CONFIGURACOES
        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->dropUnique('site_configuracoes_chave_unique');
            });
        } catch (\Throwable $e) {}
        
        try {
            Schema::table('site_configuracoes', function (Blueprint $table) {
                $table->dropUnique('site_config_user_chave_unique');
            });
        } catch (\Throwable $e) {}
        
        Schema::table('site_configuracoes', function (Blueprint $table) {
            $table->unique(['loja_id', 'chave'], 'site_configs_loja_chave_unique');
        });

        // CATEGORIAS
        try {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Throwable $e) {}
        
        Schema::table('categorias', function (Blueprint $table) {
            $table->unique(['loja_id', 'slug'], 'categorias_loja_slug_unique');
        });

        // PRODUTOS
        try {
            Schema::table('produtos', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Throwable $e) {}
        
        Schema::table('produtos', function (Blueprint $table) {
            $table->unique(['loja_id', 'slug'], 'produtos_loja_slug_unique');
        });

        // PAGINAS LEGAIS
        try {
            Schema::table('paginas_legais', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Throwable $e) {}
        
        Schema::table('paginas_legais', function (Blueprint $table) {
            $table->unique(['loja_id', 'slug'], 'paginas_legais_loja_slug_unique');
        });

        // CUPONS
        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->dropUnique('cupons_codigo_unique');
            });
        } catch (\Throwable $e) {}
        
        try {
            Schema::table('cupons', function (Blueprint $table) {
                $table->dropUnique('cupons_user_codigo_unique');
            });
        } catch (\Throwable $e) {}
        
        Schema::table('cupons', function (Blueprint $table) {
            $table->unique(['loja_id', 'codigo'], 'cupons_loja_codigo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter índices para o estado anterior
        
        foreach (['site_configuracoes', 'categorias', 'produtos', 'paginas_legais', 'cupons'] as $table) {
            try {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropUnique(['loja_id', 'chave']);
                    $table->dropUnique(['loja_id', 'slug']);
                    $table->dropUnique(['loja_id', 'codigo']);
                    $table->dropUnique('site_configs_loja_chave_unique');
                    $table->dropUnique('categorias_loja_slug_unique');
                    $table->dropUnique('produtos_loja_slug_unique');
                    $table->dropUnique('paginas_legais_loja_slug_unique');
                    $table->dropUnique('cupons_loja_codigo_unique');
                });
            } catch (\Throwable $e) {}
        }

        foreach (array_reverse($this->tables) as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('loja_id');
                });
            }
        }
    }
};
