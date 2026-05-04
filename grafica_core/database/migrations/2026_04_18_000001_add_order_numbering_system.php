<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 18/04/2026
|
| MIGRATION: Sistema de Numeração Inteligente de Pedidos
|
| Adiciona:
| - codigo_loja em lojas (prefixo para identificação)
| - numero_sequencial em pedidos (sequencial por loja)
| - codigo_pedido em pedidos (código público amigável)
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar código da loja
        Schema::table('lojas', function (Blueprint $table): void {
            if (!Schema::hasColumn('lojas', 'codigo_loja')) {
                $table->string('codigo_loja', 6)
                      ->nullable()
                      ->unique()
                      ->after('slug')
                      ->comment('Prefixo para numeração de pedidos (2-6 chars)');
            }
        });

        // 2. Gerar código para lojas existentes baseado no slug (com fallback seguro)
        DB::table('lojas')->whereNull('codigo_loja')->orderBy('id')->chunk(100, function ($lojas) {
            foreach ($lojas as $loja) {
                // Gerar código base a partir do nome (ou fallback para ID)
                $slugLimpo = Str::slug($loja->nome_fantasia, '');
                $codigo = Str::upper(Str::substr($slugLimpo, 0, 4));
                
                // FALLBACK: Se slug vazio, usar 'L' + ID da loja
                if (empty($codigo)) {
                    $codigo = 'L' . $loja->id;
                }
                
                // Garantir unicidade adicionando sufixo se necessário
                $codigoFinal = $codigo;
                $tentativa = 1;
                $maxTentativas = 99; // Evitar loop infinito
                while (DB::table('lojas')->where('codigo_loja', $codigoFinal)->exists() && $tentativa <= $maxTentativas) {
                    $codigoFinal = Str::substr($codigo, 0, 3) . $tentativa;
                    $tentativa++;
                }
                
                // Se ainda houver conflito, usar ID como sufixo
                if (DB::table('lojas')->where('codigo_loja', $codigoFinal)->exists()) {
                    $codigoFinal = 'L' . $loja->id;
                }
                
                DB::table('lojas')
                    ->where('id', $loja->id)
                    ->update(['codigo_loja' => $codigoFinal]);
            }
        });

        // 3. Adicionar campos de numeração em pedidos
        Schema::table('pedidos', function (Blueprint $table): void {
            if (!Schema::hasColumn('pedidos', 'numero_sequencial')) {
                $table->unsignedBigInteger('numero_sequencial')
                      ->nullable()
                      ->after('numero')
                      ->comment('Número sequencial único por loja');
            }

            if (!Schema::hasColumn('pedidos', 'codigo_pedido')) {
                $table->string('codigo_pedido', 20)
                      ->nullable()
                      ->after('numero_sequencial')
                      ->comment('Código público amigável: LOJA-AA-XXXXX');
            }

            // Índices para busca otimizada (verificando antes de criar)
            // Nota: Laravel não tem um hasIndex nativo simples mas podemos tentar capturar erro ou apenas fazer o try
        });

        // 4. Preencher numero_sequencial para pedidos existentes
        $lojas = DB::table('lojas')->pluck('id');

        foreach ($lojas as $lojaId) {
            $pedidos = DB::table('pedidos')
                ->where('loja_id', $lojaId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            $sequencial = 1;
            foreach ($pedidos as $pedidoId) {
                DB::table('pedidos')
                    ->where('id', $pedidoId)
                    ->update(['numero_sequencial' => $sequencial]);
                $sequencial++;
            }
        }

        // 5. Gerar codigo_pedido para pedidos existentes
        if (DB::getDriverName() === 'sqlite') {
            DB::table('pedidos')
                ->whereNotNull('numero_sequencial')
                ->orderBy('id')
                ->chunk(100, function ($pedidos): void {
                    foreach ($pedidos as $pedido) {
                        $loja = DB::table('lojas')->select('codigo_loja')->where('id', $pedido->loja_id)->first();
                        if (!$loja) {
                            continue;
                        }

                        $ano = date('y', strtotime((string) $pedido->created_at));
                        $codigoPedido = sprintf('%s-%s-%05d', (string) $loja->codigo_loja, $ano, (int) $pedido->numero_sequencial);

                        DB::table('pedidos')
                            ->where('id', $pedido->id)
                            ->update(['codigo_pedido' => $codigoPedido]);
                    }
                });
        } else {
            DB::statement(" 
                UPDATE pedidos p
                INNER JOIN lojas l ON l.id = p.loja_id
                SET p.codigo_pedido = CONCAT(
                    l.codigo_loja,
                    '-',
                    DATE_FORMAT(p.created_at, '%y'),
                    '-',
                    LPAD(p.numero_sequencial, 5, '0')
                )
                WHERE p.numero_sequencial IS NOT NULL
            ");
        }

        // 6. Tornar campos obrigatórios após preenchimento
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->unsignedBigInteger('numero_sequencial')->nullable(false)->change();
                $table->string('codigo_pedido', 20)->nullable(false)->change();
            });
        }

        // 7. Adicionar constraint de unicidade composta
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->unique(['loja_id', 'numero_sequencial'], 'uk_pedidos_loja_sequencial');
            $table->unique('codigo_pedido', 'uk_pedidos_codigo');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table): void {
            $table->dropUnique('uk_pedidos_loja_sequencial');
            $table->dropUnique('uk_pedidos_codigo');
            $table->dropIndex('idx_pedidos_loja_sequencial');
            $table->dropIndex('idx_pedidos_codigo');
            $table->dropColumn(['numero_sequencial', 'codigo_pedido']);
        });

        Schema::table('lojas', function (Blueprint $table): void {
            $table->dropUnique('lojas_codigo_loja_unique');
            $table->dropColumn('codigo_loja');
        });
    }
};
