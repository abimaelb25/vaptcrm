<?php

declare(strict_types=1);

namespace App\Http\Controllers\CMS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-10 18:31 -03:00
*/

use App\Http\Controllers\Controller;
use App\Models\SiteConfiguracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AparenciaLojaController extends Controller
{
    private const CAMPOS_IMAGEM = [
        'aparencia_logo',
        'aparencia_logo_rodape',
        'aparencia_capa',
        'aparencia_favicon',
    ];

    public function index(): View
    {
        $configs = SiteConfiguracao::all()->pluck('valor', 'chave')->toArray();
        return view('painel.aparencia.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'aparencia_cor_primaria'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_cor_secundaria' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_cor_destaque'   => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'aparencia_modo'           => ['nullable', 'in:claro,escuro'],
            'aparencia_layout_catalogo'=> ['nullable', 'in:grid,lista'],
            'aparencia_rodape_texto'   => ['nullable', 'string', 'max:500'],
            'aparencia_logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'aparencia_logo_rodape'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'aparencia_capa'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'aparencia_favicon'        => ['nullable', 'image', 'mimes:png,ico,jpg', 'max:512'],
            'empresa_nome'      => ['nullable', 'string', 'max:150'],
            'empresa_cnpj'      => ['nullable', 'string', 'max:20'],
            'empresa_telefone'  => ['nullable', 'string', 'max:25'],
            'empresa_email'     => ['nullable', 'email', 'max:150'],
            'empresa_endereco'  => ['nullable', 'string', 'max:250'],
            'empresa_cidade_uf' => ['nullable', 'string', 'max:80'],
            'empresa_cep'       => ['nullable', 'string', 'max:10'],
            'empresa_pix_chave' => ['nullable', 'string', 'max:150'],
            'empresa_pix_tipo'  => ['nullable', 'in:cpf,cnpj,email,telefone,aleatoria'],
            'empresa_site'      => ['nullable', 'url', 'max:200'],
            'empresa_instagram' => ['nullable', 'url', 'max:200'],
            'empresa_whatsapp'  => ['nullable', 'string', 'max:20'],
        ]);

        try {
            // Processa todos os campos de texto
            $camposTexto = $request->except(array_merge(['_token', '_method'], self::CAMPOS_IMAGEM));

            foreach ($camposTexto as $chave => $valor) {
                if (!str_starts_with($chave, 'aparencia_') && !str_starts_with($chave, 'empresa_')) {
                    continue;
                }
                SiteConfiguracao::updateOrCreate(
                    ['chave' => $chave],
                    ['valor' => $valor, 'tipo' => str_contains($chave, '_cor') ? 'cor' : 'texto']
                );
            }

            // Processa uploads de imagem
            foreach (self::CAMPOS_IMAGEM as $campo) {
                if ($request->hasFile($campo)) {
                    $config = SiteConfiguracao::where('chave', $campo)->first();

                    // Remove imagem anterior do disco
                    if ($config && $config->valor) {
                        Storage::disk('public')->delete($config->valor);
                    }

                    $caminho = $request->file($campo)->store('configuracoes', 'public');

                    SiteConfiguracao::updateOrCreate(
                        ['chave' => $campo],
                        ['valor' => $caminho, 'tipo' => 'imagem']
                    );
                }
            }

            Cache::forget('site_configuracoes_todas');

        } catch (\Throwable $e) {
            Log::error('Erro ao salvar aparência', ['msg' => $e->getMessage()]);
            return back()->with('erro', 'Erro ao salvar configurações: ' . $e->getMessage());
        }

        return back()->with('sucesso', 'Aparência da loja atualizada com sucesso!');
    }

    public function removerImagem(Request $request): RedirectResponse
    {
        $chave = $request->input('chave');

        if (!in_array($chave, self::CAMPOS_IMAGEM, true)) {
            abort(422, 'Campo inválido.');
        }

        $config = SiteConfiguracao::where('chave', $chave)->first();
        if ($config && $config->valor) {
            Storage::disk('public')->delete($config->valor);
            $config->update(['valor' => null]);
            Cache::forget('site_configuracoes_todas');
        }

        return back()->with('sucesso', 'Imagem removida com sucesso.');
    }
}
