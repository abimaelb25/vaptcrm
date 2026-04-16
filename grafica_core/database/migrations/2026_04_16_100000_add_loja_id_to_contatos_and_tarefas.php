<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16
| Descrição: Adiciona loja_id às tabelas contatos e tarefas para isolamento multi-tenant
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar loja_id à tabela contatos (se não existir)
        if (!Schema::hasColumn('contatos', 'loja_id')) {
            Schema::table('contatos', function (Blueprint $table) {
                $table->foreignId('loja_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('lojas')
                    ->cascadeOnDelete();
                
                $table->index('loja_id');
            });
        }

        // Adicionar loja_id à tabela tarefas (se não existir)
        if (!Schema::hasColumn('tarefas', 'loja_id')) {
            Schema::table('tarefas', function (Blueprint $table) {
                $table->foreignId('loja_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('lojas')
                    ->cascadeOnDelete();
                
                $table->index('loja_id');
            });
        }

        // Backfill: Associar contatos existentes à loja do cliente (compatível com SQLite e MySQL)
        $contatos = DB::table('contatos')
            ->whereNull('loja_id')
            ->whereNotNull('cliente_id')
            ->get();
        
        foreach ($contatos as $contato) {
            $cliente = DB::table('clientes')->where('id', $contato->cliente_id)->first();
            if ($cliente && $cliente->loja_id) {
                DB::table('contatos')
                    ->where('id', $contato->id)
                    ->update(['loja_id' => $cliente->loja_id]);
            }
        }

        // Backfill: Associar tarefas existentes à loja do responsável
        $tarefas = DB::table('tarefas')
            ->whereNull('loja_id')
            ->whereNotNull('responsavel_id')
            ->get();
        
        foreach ($tarefas as $tarefa) {
            $usuario = DB::table('usuarios')->where('id', $tarefa->responsavel_id)->first();
            if ($usuario && $usuario->loja_id) {
                DB::table('tarefas')
                    ->where('id', $tarefa->id)
                    ->update(['loja_id' => $usuario->loja_id]);
            }
        }

        // Se ainda houver registros órfãos, associar à primeira loja (caso de desenvolvimento)
        $primeiraLojaId = DB::table('lojas')->min('id');
        
        if ($primeiraLojaId) {
            DB::table('contatos')
                ->whereNull('loja_id')
                ->update(['loja_id' => $primeiraLojaId]);
            
            DB::table('tarefas')
                ->whereNull('loja_id')
                ->update(['loja_id' => $primeiraLojaId]);
        }
    }

    public function down(): void
    {
        Schema::table('contatos', function (Blueprint $table) {
            $table->dropForeign(['loja_id']);
            $table->dropColumn('loja_id');
        });

        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropForeign(['loja_id']);
            $table->dropColumn('loja_id');
        });
    }
};
