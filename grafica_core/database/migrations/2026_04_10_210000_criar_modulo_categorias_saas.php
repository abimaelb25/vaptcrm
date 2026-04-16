<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10 17:29 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 100);
            $table->string('slug', 120)->unique()->index();
            $table->string('descricao', 255)->nullable();
            $table->text('texto_destaque')->nullable();
            $table->string('banner')->nullable();
            $table->unsignedSmallInteger('ordem_exibicao')->default(0)->index();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Vincula produto a uma categoria relacional (mantém a coluna string legada para compatibilidade)
        Schema::table('produtos', function (Blueprint $table): void {
            $table->foreignId('categoria_id')
                ->nullable()
                ->after('categoria')
                ->constrained('categorias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('categoria_id');
        });

        Schema::dropIfExists('categorias');
    }
};
