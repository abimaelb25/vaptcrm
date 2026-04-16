<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 03:50 (correção: trial centralizado no TenantProvisioningService)
| Descrição: Controlador responsável pelo fluxo público de registro de novas lojas (Onboarding).
*/

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;
use App\Models\Loja;
use App\Models\SaaS\Plano;
use App\Services\SaaS\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OnboardingController extends Controller
{
    /**
     * @param TenantProvisioningService $provisioningService
     */
    public function __construct(
        protected TenantProvisioningService $provisioningService
    ) {}

    /**
     * Exibe o formulário de registro para um plano específico.
     */
    public function show(string $plano_slug = 'bronze'): View
    {
        $plano = Plano::where('slug', $plano_slug)->where('ativo', true)->firstOrFail();
        
        return view('saas.onboarding.register', [
            'plano' => $plano
        ]);
    }

    /**
     * Processa a criação da loja, usuário admin e provisionamento inicial.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nome_fantasia'     => 'required|string|max:100|min:3',
            'responsavel_nome'   => 'required|string|max:100',
            'responsavel_email'  => 'required|email|unique:usuarios,email',
            'responsavel_whatsapp' => 'nullable|string|max:20',
            'senha'              => 'required|string|min:8|confirmed',
            'plano_id'           => 'required|exists:saas_planos,id',
            'termos'             => 'accepted'
        ], [
            'responsavel_email.unique' => 'Este e-mail já está sendo utilizado em nossa plataforma.',
            'termos.accepted'          => 'Você deve aceitar os termos de uso para continuar.'
        ]);

        $slug = Str::slug($request->nome_fantasia);
        
        // Garante slug único para a loja
        if (Loja::where('slug', $slug)->exists()) {
             $slug .= '-' . Str::lower(Str::random(4));
        }

        return DB::transaction(function () use ($request, $slug) {
            // 1. Criar a Loja
            $loja = Loja::create([
                'nome_fantasia'        => $request->nome_fantasia,
                'slug'                 => $slug,
                'responsavel_nome'     => $request->responsavel_nome,
                'responsavel_email'    => $request->responsavel_email,
                'responsavel_whatsapp' => $request->responsavel_whatsapp,
                'status'               => 'trial',
                'plano_id'             => $request->plano_id,
                // trial_ends_at é definido centralmente no TenantProvisioningService
            ]);

            // 2. Preparar dados do Admin
            $adminData = [
                'nome'  => $request->responsavel_nome,
                'email' => $request->responsavel_email,
                'senha' => Hash::make($request->senha),
            ];

            // 3. Provisionar estrutura inicial via serviço
            $admin = $this->provisioningService->provision($loja, $adminData, (int)$request->plano_id);

            // 4. Autenticar e redirecionar
            Auth::login($admin);

            return redirect()->route('admin.dashboard')
                ->with('success', "Bem-vindo(a), {$request->responsavel_nome}! Sua loja '{$loja->nome_fantasia}' foi criada e está pronta para uso.");
        });
    }
}
