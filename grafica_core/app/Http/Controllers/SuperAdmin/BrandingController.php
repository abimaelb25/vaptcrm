<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-16 00:05
*/

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    private array $camposImagem = [
        'plataforma_logo',
        'plataforma_logo_dark',
        'plataforma_favicon',
    ];

    private array $camposTexto = [
        'plataforma_nome',
        'plataforma_cor_primaria',
        'plataforma_cor_secundaria',
        'plataforma_email_suporte',
        'plataforma_whatsapp_suporte',
    ];

    public function index(): View
    {
        $configs = ConfiguracaoSistema::pluck('valor', 'chave')->toArray();
        return view('super-admin.branding.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'plataforma_logo'         => ['nullable', 'image', 'max:2048'],
            'plataforma_logo_dark'    => ['nullable', 'image', 'max:2048'],
            'plataforma_favicon'      => ['nullable', 'image', 'max:512'],
            'plataforma_nome'         => ['required', 'string', 'max:100'],
            'plataforma_cor_primaria' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'plataforma_cor_secundaria' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'plataforma_email_suporte' => ['nullable', 'email', 'max:200'],
            'plataforma_whatsapp_suporte' => ['nullable', 'string', 'max:30'],
        ]);

        foreach ($this->camposImagem as $campo) {
            if ($request->hasFile($campo)) {
                $configAtual = ConfiguracaoSistema::where('chave', $campo)->first();
                if ($configAtual?->valor) {
                    Storage::disk('public')->delete($configAtual->valor);
                }

                $caminho = $request->file($campo)->store('plataforma', 'public');

                ConfiguracaoSistema::updateOrCreate(
                    ['chave' => $campo],
                    ['valor' => $caminho]
                );
            }
        }

        foreach ($this->camposTexto as $campo) {
            if ($request->has($campo)) {
                ConfiguracaoSistema::updateOrCreate(
                    ['chave' => $campo],
                    ['valor' => $request->input($campo) ?? '']
                );
            }
        }

        Cache::forget('branding_plataforma');

        return back()->with('success', 'Identidade visual da plataforma atualizada com sucesso.');
    }
}
