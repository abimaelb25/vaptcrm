<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 00:45 BRT
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('depoimentos', function (Blueprint $table) {
            // Renomear colunas existentes para o novo padrão se elas existirem
            if (Schema::hasColumn('depoimentos', 'cliente_nome')) {
                $table->renameColumn('cliente_nome', 'nome_autor');
            }
            if (Schema::hasColumn('depoimentos', 'cliente_empresa')) {
                $table->renameColumn('cliente_empresa', 'empresa_autor');
            }
            if (Schema::hasColumn('depoimentos', 'texto')) {
                $table->renameColumn('texto', 'depoimento_texto');
            }
            if (Schema::hasColumn('depoimentos', 'avatar')) {
                $table->renameColumn('avatar', 'avatar_path');
            }
            if (Schema::hasColumn('depoimentos', 'ativo')) {
                $table->renameColumn('ativo', 'publicado');
            }
            if (Schema::hasColumn('depoimentos', 'ordem')) {
                $table->renameColumn('ordem', 'ordem_exibicao');
            }
        });

        Schema::table('depoimentos', function (Blueprint $table) {
            // Adicionar novas colunas
            if (!Schema::hasColumn('depoimentos', 'contexto')) {
                $table->enum('contexto', ['loja', 'plataforma'])->default('loja')->after('loja_id');
            }
            if (!Schema::hasColumn('depoimentos', 'cargo_autor')) {
                $table->string('cargo_autor')->nullable()->after('nome_autor');
            }
            if (!Schema::hasColumn('depoimentos', 'cidade_autor')) {
                $table->string('cidade_autor')->nullable()->after('empresa_autor');
            }
            if (!Schema::hasColumn('depoimentos', 'nota')) {
                $table->tinyInteger('nota')->nullable()->after('depoimento_texto');
            }
            if (!Schema::hasColumn('depoimentos', 'titulo')) {
                $table->string('titulo')->nullable()->after('nota');
            }
            if (!Schema::hasColumn('depoimentos', 'destaque')) {
                $table->boolean('destaque')->default(false)->after('publicado');
            }
        });

        // Garantir que todos os depoimentos atuais sejam do contexto loja
        DB::table('depoimentos')->whereNull('contexto')->update(['contexto' => 'loja']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depoimentos', function (Blueprint $table) {
            $table->dropColumn(['contexto', 'cargo_autor', 'cidade_autor', 'nota', 'titulo', 'destaque']);
            
            // Reverter nomes se necessário (opcional, mas para manter simetria)
            $table->renameColumn('nome_autor', 'cliente_nome');
            $table->renameColumn('empresa_autor', 'cliente_empresa');
            $table->renameColumn('depoimento_texto', 'texto');
            $table->renameColumn('avatar_path', 'avatar');
            $table->renameColumn('publicado', 'ativo');
            $table->renameColumn('ordem_exibicao', 'ordem');
        });
    }
};
