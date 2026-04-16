<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-11 00:21
*/

use App\Http\Controllers\Controller;
use App\Models\SiteConfiguracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AparenciaController extends Controller
{
    private array $camposImagem = [
        'aparencia_logo',
        'aparencia_logo_rodape',
        'aparencia_capa',
        'aparencia_favicon',
    ];

    private array $camposTexto = [
        'aparencia_cor_primaria',
        'aparencia_cor_secundaria',
        'aparencia_cor_destaque',
        'aparencia_modo',
        'aparencia_layout_catalogo',
        'aparencia_rodape_texto',
        'empresa_nome',
        'empresa_cnpj',
        'empresa_telefone',
        'empresa_email',
        'empresa_endereco',
        'empresa_cidade_uf',
        'empresa_cep',
        'empresa_pix_chave',
        'empresa_pix_tipo',
        'empresa_site',
        'empresa_instagram',
        'empresa_whatsapp',
    ];

    public function index(): View
    {
        $configs = SiteConfiguracao::all()->pluck('valor', 'chave')->toArray();

        return view('painel.aparencia.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'aparencia_logo'         => ['nullable', 'image', 'max:2048'],
            'aparencia_logo_rodape'  => ['nullable', 'image', 'max:2048'],
            'aparencia_capa'         => ['nullable', 'image', 'max:4096'],
            'aparencia_favicon'      => ['nullable', 'image', 'max:512'],
            'aparencia_cor_primaria' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_cor_secundaria' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_cor_destaque' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_modo'         => ['nullable', 'in:claro,escuro'],
            'aparencia_layout_catalogo' => ['nullable', 'in:grid,lista,misto'],
            'aparencia_rodape_texto' => ['nullable', 'string', 'max:500'],
            'empresa_nome'           => ['nullable', 'string', 'max:200'],
            'empresa_cnpj'           => ['nullable', 'string', 'max:20'],
            'empresa_telefone'       => ['nullable', 'string', 'max:30'],
            'empresa_email'          => ['nullable', 'email', 'max:200'],
            'empresa_endereco'       => ['nullable', 'string', 'max:300'],
            'empresa_cidade_uf'      => ['nullable', 'string', 'max:100'],
            'empresa_cep'            => ['nullable', 'string', 'max:10'],
            'empresa_pix_chave'      => ['nullable', 'string', 'max:100'],
            'empresa_pix_tipo'       => ['nullable', 'in:cpf,cnpj,email,telefone,aleatoria'],
            'empresa_site'           => ['nullable', 'url', 'max:300'],
            'empresa_instagram'      => ['nullable', 'string', 'max:200'],
            'empresa_whatsapp'       => ['nullable', 'string', 'max:20'],
        ]);

        foreach ($this->camposImagem as $campo) {
            if ($request->hasFile($campo)) {
                $configAtual = SiteConfiguracao::where('chave', $campo)->first();
                if ($configAtual?->valor) {
                    Storage::disk('public')->delete($configAtual->valor);
                }

                $caminho = $request->file($campo)->store('aparencia', 'public');

                SiteConfiguracao::updateOrCreate(
                    ['chave' => $campo],
                    ['valor' => $caminho, 'tipo' => 'imagem']
                );
            }

            if ($request->boolean("remover_{$campo}")) {
                $configAtual = SiteConfiguracao::where('chave', $campo)->first();
                if ($configAtual?->valor) {
                    Storage::disk('public')->delete($configAtual->valor);
                    $configAtual->update(['valor' => null]);
                }
            }
        }

        foreach ($this->camposTexto as $campo) {
            if ($request->has($campo)) {
                $tipo = str_starts_with($campo, 'aparencia_cor_') ? 'cor' : 'texto';
                if (in_array($campo, ['empresa_site', 'empresa_instagram'])) {
                    $tipo = 'url';
                }

                SiteConfiguracao::updateOrCreate(
                    ['chave' => $campo],
                    ['valor' => $request->input($campo) ?? '', 'tipo' => $tipo]
                );
            }
        }

        $lojaId = auth()->user()?->loja_id ?? 'global';
        Cache::forget("site_configs_{$lojaId}");
        Cache::forget('site_configuracoes_todas');

        return back()->with('sucesso', 'Aparência e dados da empresa atualizados com sucesso.');
    }
}
