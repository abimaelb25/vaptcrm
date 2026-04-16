<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:50 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha');
            $table->string('perfil');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('empresa')->nullable();
            $table->string('cpf_cnpj', 20)->nullable();
            $table->string('telefone', 20)->nullable()->index();
            $table->string('whatsapp', 20)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('endereco')->nullable();
            $table->string('cidade')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('origem_lead')->nullable();
            $table->string('status')->default('novo_contato')->index();
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('categoria')->index();
            $table->string('descricao_curta', 255)->nullable();
            $table->text('descricao_completa')->nullable();
            $table->string('imagem_principal')->nullable();
            $table->decimal('preco_base', 10, 2)->nullable();
            $table->string('prazo_estimado')->nullable();
            $table->boolean('destaque')->default(false)->index();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pedidos', function (Blueprint $table): void {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('status')->default('orcamento')->index();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('tipo_total', ['automatico', 'manual'])->default('automatico');
            $table->enum('tipo_entrega', ['retirada', 'entrega_local', 'entrega_agendada'])->default('retirada');
            $table->decimal('valor_frete', 10, 2)->default(0);
            $table->string('forma_pagamento')->nullable();
            $table->string('gateway_pagamento')->nullable();
            $table->date('prazo_entrega')->nullable();
            $table->foreignId('responsavel_id')->constrained('usuarios');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('itens_pedido', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
            $table->string('descricao_item');
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('contatos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->enum('tipo_contato', ['whatsapp', 'ligacao', 'email', 'presencial']);
            $table->text('resumo');
            $table->string('proximo_passo')->nullable();
            $table->date('data_retorno')->nullable();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamps();
        });

        Schema::create('pagamentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('gateway')->default('asaas');
            $table->string('metodo')->default('pix');
            $table->decimal('valor', 10, 2);
            $table->string('status')->default('pendente')->index();
            $table->text('codigo_pix')->nullable();
            $table->longText('qr_code')->nullable();
            $table->string('transaction_id')->nullable()->index();
            $table->string('assinatura_gateway')->nullable();
            $table->json('payload_original')->nullable();
            $table->timestamps();
        });

        Schema::create('historicos_pedido', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('status_anterior')->nullable();
            $table->string('status_novo');
            $table->text('descricao')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historicos_pedido');
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('contatos');
        Schema::dropIfExists('itens_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('usuarios');
    }
};
