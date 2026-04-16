<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona avatar na tabela usuarios se não existir
        if (!Schema::hasColumn('usuarios', 'avatar')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('email');
            });
        }

        // Armazenamento de Documentos de Usuários (Acesso Privado)
        if (!Schema::hasTable('documentos_usuarios')) {
            Schema::create('documentos_usuarios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->string('tipo_documento'); // rg, cpf, certidao_nascimento, certidao_casamento
                $table->string('caminho_arquivo'); // Localização e Storage Private
                $table->string('nome_original'); // Nome real do arquivo no computador do usuário
                $table->timestamps();
            });
        }

        // Tabela para gerenciar solicitações de alteração de cadastro
        if (!Schema::hasTable('solicitacao_atualizacoes')) {
            Schema::create('solicitacao_atualizacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->json('dados_antigos')->nullable();
                $table->json('dados_novos');
                $table->enum('status', ['pendente', 'aprovada', 'rejeitada'])->default('pendente');
                $table->foreignId('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
                $table->text('motivo_rejeicao')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao_atualizacoes');
        Schema::dropIfExists('documentos_usuarios');
        
        if (Schema::hasColumn('usuarios', 'avatar')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }
};
