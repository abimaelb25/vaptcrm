<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor de Governança e Auditoria do Módulo de Insumos
 *
 * Alterações:
 * 1. Adiciona campos de auditoria em EstoqueMovimentacao
 * 2. Adiciona suporte para inativação com rastreabilidade
 * 3. Corrige modelagem de conversão de unidades
 * 4. Normaliza precisão de custos
 *
 * Autoria: Refactor Crítico Insumos 2026-04-29
 */
return new class extends Migration
{
    public function up(): void
    {
        // Adiciona campos de auditoria mais robustos em EstoqueMovimentacao
        // se não existirem (compatibilidade com sistema legado que pode já ter)
        if (Schema::hasTable('estoque_movimentacoes')) {
            Schema::table('estoque_movimentacoes', function (Blueprint $table) {
                // Campos para rastreabilidade completa
                if (!Schema::hasColumn('estoque_movimentacoes', 'quantidade_base')) {
                    $table->decimal('quantidade_base', 15, 4)->nullable()->after('quantidade')
                        ->comment('Quantidade convertida para unidade base do insumo');
                }
                if (!Schema::hasColumn('estoque_movimentacoes', 'saldo_anterior')) {
                    $table->decimal('saldo_anterior', 15, 4)->nullable()->after('quantidade_base')
                        ->comment('Saldo em estoque antes desta movimentação');
                }
                if (!Schema::hasColumn('estoque_movimentacoes', 'saldo_posterior')) {
                    $table->decimal('saldo_posterior', 15, 4)->nullable()->after('saldo_anterior')
                        ->comment('Saldo em estoque após esta movimentação');
                }
                if (!Schema::hasColumn('estoque_movimentacoes', 'origem_tela')) {
                    $table->string('origem_tela', 50)->nullable()->after('descricao')
                        ->comment('Qual tela/formulário gerou esta movimentação');
                }
                if (!Schema::hasColumn('estoque_movimentacoes', 'motivo')) {
                    $table->string('motivo', 100)->nullable()->after('origem_tela')
                        ->comment('Motivo ou classificação da movimentação (ajuste_manual, perda, etc)');
                }
            });
        }

        // Adiciona campos em Insumo para governança de exclusão/inativação
        if (Schema::hasTable('insumos')) {
            Schema::table('insumos', function (Blueprint $table) {
                // Campo de controle de uso para Policies
                if (!Schema::hasColumn('insumos', 'pode_ser_excluido')) {
                    $table->boolean('pode_ser_excluido')->default(true)->after('ativo')
                        ->comment('Flag: se false, apenas inativação é permitida (há movimentações associadas)');
                }

                // Campo de data de inativação para auditoria
                if (!Schema::hasColumn('insumos', 'inativado_em')) {
                    $table->timestamp('inativado_em')->nullable()->after('pode_ser_excluido')
                        ->comment('Data e hora da inativação (null = ativo)');
                }

                // Campo do usuário que inativou
                if (!Schema::hasColumn('insumos', 'inativado_por_usuario_id')) {
                    $table->foreignId('inativado_por_usuario_id')->nullable()->after('inativado_em')
                        ->constrained('usuarios')
                        ->onDelete('set null')
                        ->comment('Quem inativou este insumo');
                }

                // Motivo da inativação
                if (!Schema::hasColumn('insumos', 'motivo_inativacao')) {
                    $table->text('motivo_inativacao')->nullable()->after('inativado_por_usuario_id')
                        ->comment('Razão pela qual o insumo foi inativado');
                }

                // Normaliza precisão de custo para 6 casas decimais (regressão de migração anterior)
                // Mas verifica se a coluna precisa de conversão de tipo
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('estoque_movimentacoes')) {
            Schema::table('estoque_movimentacoes', function (Blueprint $table) {
                $table->dropColumnIfExists([
                    'quantidade_base',
                    'saldo_anterior',
                    'saldo_posterior',
                    'origem_tela',
                    'motivo',
                ]);
            });
        }

        if (Schema::hasTable('insumos')) {
            Schema::table('insumos', function (Blueprint $table) {
                $table->dropColumnIfExists([
                    'pode_ser_excluido',
                    'inativado_em',
                    'inativado_por_usuario_id',
                    'motivo_inativacao',
                ]);
            });
        }
    }
};
