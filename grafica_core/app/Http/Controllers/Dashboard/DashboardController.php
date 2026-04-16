<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 14/04/2026 (Refatoração para Dashboard Dinâmico)
*/

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Gerencia a exibição da dashboard central baseada no perfil e permissões.
     */
    public function __invoke(DashboardService $service)
    {
        $user = auth()->user();

        // Abimael Borges | https://abimaelborges.adv.br | 2026-04-16 01:10 BRT
        if ($user->isSuperAdmin() && $user->loja_id === null) {
            return redirect()->route('superadmin.dashboard');
        }
        
        // Resolve o slug estável do perfil e coleta os dados agregados
        $perfilSlug = $service->getProfileSlug($user);
        $data = $service->getDataForProfile($user);

        // Mapeamento de views por perfil
        $viewPath = "painel.dashboards.{$perfilSlug}";

        // Fallback de segurança caso a view específica não exista
        if (!view()->exists($viewPath)) {
            return view('painel.dashboards.fallback', $data);
        }

        return view($viewPath, $data);
    }
}
