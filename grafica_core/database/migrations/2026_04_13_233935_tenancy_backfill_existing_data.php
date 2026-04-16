<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 13/04/2026 23:48
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'usuarios', 'clientes', 'produtos', 'categorias', 'pedidos', 
        'itens_pedido', 'contatos', 'pagamentos', 'historicos_pedido', 
        'auditorias', 'tarefas', 'site_configuracoes', 'banners', 
        'depoimentos', 'paginas_legais', 'metricas_site', 'cupons', 
        'integracoes_pagamento', 'movimentacoes_financeiras', 'caixas', 
        'saas_assinaturas'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Criar a Loja Padrão (Master)
        $planoOuro = DB::table('saas_planos')->where('slug', 'ouro')->first();
        
        $lojaId = DB::table('lojas')->insertGetId([
            'nome_fantasia' => 'Loja Master (Legada)',
            'slug' => 'loja-master',
            'responsavel_nome' => 'Administrador',
            'responsavel_email' => 'admin@vaptcrm.com.br',
            'status' => 'ativa',
            'plano_id' => $planoOuro?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Vincular todos os registros existentes à Loja Master
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                DB::table($tableName)->whereNull('loja_id')->update(['loja_id' => $lojaId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for backfill as it's a data migration
    }
};
