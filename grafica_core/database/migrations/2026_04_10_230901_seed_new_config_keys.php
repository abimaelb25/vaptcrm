<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10 23:10
*/

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $configs = [
            ['chave' => 'loja_nome', 'valor' => 'Gráfica Vapt Vupt', 'tipo' => 'texto'],
            ['chave' => 'loja_subtitulo', 'valor' => 'Sua solução rápida em impressões', 'tipo' => 'texto'],
            ['chave' => 'loja_slug', 'valor' => 'vaptvupt', 'tipo' => 'texto'],
        ];

        foreach ($configs as $config) {
            DB::table('site_configuracoes')->insertOrIgnore(array_merge($config, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('site_configuracoes')->whereIn('chave', ['loja_nome', 'loja_subtitulo', 'loja_slug'])->delete();
    }
};
