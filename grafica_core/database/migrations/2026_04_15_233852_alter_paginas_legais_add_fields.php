<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 23:38
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paginas_legais', function (Blueprint $table) {
            if (!Schema::hasColumn('paginas_legais', 'tipo')) {
                $table->string('tipo', 100)->default('personalizada')->after('slug');
            }
            if (!Schema::hasColumn('paginas_legais', 'resumo')) {
                $table->text('resumo')->nullable()->after('conteudo');
            }
            if (!Schema::hasColumn('paginas_legais', 'exibir_no_rodape')) {
                $table->boolean('exibir_no_rodape')->default(true)->after('ativa');
            }
            if (!Schema::hasColumn('paginas_legais', 'ordem_exibicao')) {
                $table->integer('ordem_exibicao')->default(0)->after('exibir_no_rodape');
            }
            if (!Schema::hasColumn('paginas_legais', 'pagina_sistema')) {
                $table->boolean('pagina_sistema')->default(false)->after('ordem_exibicao');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paginas_legais', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'resumo', 'exibir_no_rodape', 'ordem_exibicao', 'pagina_sistema']);
        });
    }
};
