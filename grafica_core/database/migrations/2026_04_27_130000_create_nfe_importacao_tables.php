<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_fiscais_entrada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->string('chave_nfe', 44);
            $table->string('numero', 20)->nullable();
            $table->string('serie', 20)->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->string('xml_path')->nullable();
            $table->enum('status_importacao', ['confirmada', 'cancelada'])->default('confirmada');
            $table->foreignId('usuario_responsavel_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->unique(['loja_id', 'chave_nfe'], 'uniq_doc_fiscal_loja_chave');
            $table->index(['loja_id', 'numero', 'serie']);
        });

        Schema::create('nfe_importacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->foreignId('documento_fiscal_entrada_id')->nullable()->constrained('documentos_fiscais_entrada')->nullOnDelete();
            $table->string('chave_nfe', 44)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('serie', 20)->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->string('xml_path');
            $table->enum('status', ['preview', 'confirmada', 'cancelada', 'erro'])->default('preview');
            $table->json('payload_json')->nullable();
            $table->json('alertas_json')->nullable();
            $table->timestamp('confirmada_em')->nullable();
            $table->timestamps();

            $table->index(['loja_id', 'status']);
            $table->index(['loja_id', 'chave_nfe']);
        });

        Schema::create('documentos_fiscais_entrada_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('documento_id')->constrained('documentos_fiscais_entrada')->cascadeOnDelete();
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->nullOnDelete();
            $table->string('codigo_fornecedor')->nullable();
            $table->string('descricao');
            $table->string('ncm', 20)->nullable();
            $table->string('cfop', 10)->nullable();
            $table->string('unidade', 20)->nullable();
            $table->decimal('quantidade', 15, 4)->default(0);
            $table->decimal('valor_unitario', 15, 6)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->json('impostos_json')->nullable();
            $table->enum('acao_definida', ['criar', 'vincular', 'ignorar'])->default('ignorar');
            $table->timestamps();

            $table->index(['loja_id', 'documento_id']);
            $table->index(['loja_id', 'insumo_id']);
        });

        Schema::create('fornecedor_produto_mapeamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->cascadeOnDelete();
            $table->string('codigo_fornecedor')->nullable();
            $table->string('descricao_fornecedor');
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->unsignedTinyInteger('confianca')->default(100);
            $table->timestamps();

            $table->unique(['loja_id', 'fornecedor_id', 'codigo_fornecedor'], 'uniq_map_fornecedor_codigo');
            $table->index(['loja_id', 'insumo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedor_produto_mapeamentos');
        Schema::dropIfExists('documentos_fiscais_entrada_itens');
        Schema::dropIfExists('documentos_fiscais_entrada');
        Schema::dropIfExists('nfe_importacoes');
    }
};
