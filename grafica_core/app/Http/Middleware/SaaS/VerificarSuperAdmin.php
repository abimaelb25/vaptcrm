<?php

declare(strict_types=1);

namespace App\Http\Middleware\SaaS;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 14/04/2026 04:20
| Descrição: Middleware para proteger rotas exclusivas do Super Administrador SaaS.
*/

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerificarSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            // Se for um usuário normal tentando acessar área SaaS, mostra erro 403
            abort(403, 'Acesso não autorizado. Área exclusiva para administração da plataforma.');
        }

        return $next($request);
    }
}
