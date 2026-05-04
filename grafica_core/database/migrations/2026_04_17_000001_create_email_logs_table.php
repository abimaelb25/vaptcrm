<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 17/04/2026
| Descrição: Tabela de logs de envio de e-mails para auditoria e rastreamento.
*/

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->string('tipo', 60)->index();  // pedido_status, boas_vindas, recuperacao_senha
            $table->string('destinatario_email');
            $table->string('destinatario_nome')->nullable();
            $table->string('assunto');
            $table->string('status', 20)->default('enviado')->index(); // enviado, falhou, pendente
            $table->nullableMorphs('referencia'); // Polimórfico: Pedido, Loja, Usuario etc.
            $table->text('erro')->nullable(); // Mensagem de erro em caso de falha
            $table->json('metadata')->nullable(); // Dados extras (ex: pedido_numero, plano_nome)
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
