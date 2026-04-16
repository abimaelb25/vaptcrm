<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-06 01:40 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->string('tipo')->default('texto'); // texto, url, html, etc
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('subtitulo')->nullable();
            $table->string('imagem');
            $table->string('link')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('depoimentos', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nome');
            $table->string('cliente_empresa')->nullable();
            $table->text('texto');
            $table->string('avatar')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('paginas_legais', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->longText('conteudo');
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
        
        // Seed default settings to prevent crashes
        DB::table('site_configuracoes')->insert([
            ['chave' => 'contato_telefone', 'valor' => '(75) 9 9927-9354', 'tipo' => 'texto', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'contato_whatsapp', 'valor' => '5575999279354', 'tipo' => 'texto', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'contato_email', 'valor' => 'pedido@graficavaptvupt.com.br', 'tipo' => 'texto', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'endereco', 'valor' => '3ª Travessa Santa Maria, 299 - Juracy Magalhães - Jardim Petrolar, Alagoinhas - BA, 48005-738', 'tipo' => 'texto', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'horario_atendimento', 'valor' => 'Seg a Sex das 8h às 18h', 'tipo' => 'texto', 'created_at' => now(), 'updated_at' => now()],
            ['chave' => 'links_sociais_instagram', 'valor' => '#', 'tipo' => 'url', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Seed some mocked testimonials just to have content natively available
        DB::table('depoimentos')->insert([
            [
                'cliente_nome' => 'Amanda Silva', 'cliente_empresa' => 'Loja da Amanda',
                'texto' => 'Fiz todos os panfletos e cartões da minha loja com a Gráfica Vapt Vupt. A qualidade é excepcional e a nitidez das cores impressiona. Sem falar no prazo de entrega que foi cumprido à risca. Super indico!', 
                'avatar' => null, 'ativo' => true, 'ordem' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'cliente_nome' => 'Carlos Pereira', 'cliente_empresa' => 'Consultoria CP',
                'texto' => 'O sistema de orçamentos e acompanhamento é muito fácil de usar. Precisava de urgência nas banners para um evento e o time resolveu meu problema na hora certa!', 
                'avatar' => null, 'ativo' => true, 'ordem' => 2, 'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paginas_legais');
        Schema::dropIfExists('depoimentos');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('site_configuracoes');
    }
};
