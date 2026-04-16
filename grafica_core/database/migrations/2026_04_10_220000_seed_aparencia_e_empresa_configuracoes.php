<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10 18:31 -03:00
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sementeia as novas chaves de aparência e dados institucionais na tabela existente
        $novas = [
            // Identidade visual
            ['chave' => 'aparencia_cor_primaria',   'valor' => '#FF7A00', 'tipo' => 'cor'],
            ['chave' => 'aparencia_cor_secundaria',  'valor' => '#1E293B', 'tipo' => 'cor'],
            ['chave' => 'aparencia_cor_destaque',    'valor' => '#F59E0B', 'tipo' => 'cor'],
            ['chave' => 'aparencia_modo',            'valor' => 'claro',   'tipo' => 'texto'],
            ['chave' => 'aparencia_layout_catalogo', 'valor' => 'grid',    'tipo' => 'texto'],
            ['chave' => 'aparencia_logo',            'valor' => null,      'tipo' => 'imagem'],
            ['chave' => 'aparencia_logo_rodape',     'valor' => null,      'tipo' => 'imagem'],
            ['chave' => 'aparencia_capa',            'valor' => null,      'tipo' => 'imagem'],
            ['chave' => 'aparencia_favicon',         'valor' => null,      'tipo' => 'imagem'],
            ['chave' => 'aparencia_rodape_texto',    'valor' => 'Soluções gráficas rápidas, seguras e com qualidade premium para sua marca.', 'tipo' => 'texto'],

            // Dados institucionais (para PDF e rodapé)
            ['chave' => 'empresa_nome',       'valor' => 'Gráfica Vapt Vupt',           'tipo' => 'texto'],
            ['chave' => 'empresa_cnpj',       'valor' => null,                           'tipo' => 'texto'],
            ['chave' => 'empresa_telefone',   'valor' => '(75) 9 9927-9354',            'tipo' => 'texto'],
            ['chave' => 'empresa_email',      'valor' => 'pedido@graficavaptvupt.com.br','tipo' => 'texto'],
            ['chave' => 'empresa_endereco',   'valor' => '3ª Travessa Santa Maria, 299 - Alagoinhas - BA', 'tipo' => 'texto'],
            ['chave' => 'empresa_cidade_uf',  'valor' => 'Alagoinhas - BA',             'tipo' => 'texto'],
            ['chave' => 'empresa_cep',        'valor' => null,                           'tipo' => 'texto'],
            ['chave' => 'empresa_pix_chave',  'valor' => null,                           'tipo' => 'texto'],
            ['chave' => 'empresa_pix_tipo',   'valor' => 'cpf',                          'tipo' => 'texto'],
            ['chave' => 'empresa_site',       'valor' => null,                           'tipo' => 'url'],
            ['chave' => 'empresa_instagram',  'valor' => null,                           'tipo' => 'url'],
            ['chave' => 'empresa_whatsapp',   'valor' => '5575999279354',               'tipo' => 'texto'],
        ];

        foreach ($novas as $item) {
            // Insere apenas se a chave ainda não existir (preserva configurações existentes)
            DB::table('site_configuracoes')->insertOrIgnore([
                'chave'      => $item['chave'],
                'valor'      => $item['valor'],
                'tipo'       => $item['tipo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $chaves = [
            'aparencia_cor_primaria', 'aparencia_cor_secundaria', 'aparencia_cor_destaque',
            'aparencia_modo', 'aparencia_layout_catalogo', 'aparencia_logo', 'aparencia_logo_rodape',
            'aparencia_capa', 'aparencia_favicon', 'aparencia_rodape_texto',
            'empresa_nome', 'empresa_cnpj', 'empresa_telefone', 'empresa_email',
            'empresa_endereco', 'empresa_cidade_uf', 'empresa_cep',
            'empresa_pix_chave', 'empresa_pix_tipo', 'empresa_site', 'empresa_instagram', 'empresa_whatsapp',
        ];

        DB::table('site_configuracoes')->whereIn('chave', $chaves)->delete();
    }
};
