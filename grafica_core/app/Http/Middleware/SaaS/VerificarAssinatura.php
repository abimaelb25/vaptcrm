<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20 (atualizado: bloqueio por inadimplência)
*/

use App\Services\SaaS\SaaSService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAssinatura
{
    public function __construct(
        protected SaaSService $saasService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignora rotas de checkout, billing e webhooks para evitar loops
        if ($request->is('painel/assinatura*') || $request->is('pagamentos*') || $request->is('api/webhooks*') || $request->is('loja-bloqueada')) {
            return $next($request);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->loja && $user->loja->estaBloqueada()) {
            return redirect()->route('admin.loja.bloqueada');
        }

        $assinatura = $this->saasService->getAssinatura();

        if ($assinatura->expirada()) {
            return redirect()
                ->route('admin.billing.index')
                ->with('warning', 'Sua assinatura expirou. Renove para continuar utilizando todos os recursos.');
        }

        return $next($request);
    }
}
