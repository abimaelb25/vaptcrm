<?php

declare(strict_types=1);

namespace App\Http\Middleware;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:00 -03:00
*/

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPerfil
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $usuario = $request->user();

        if ($usuario === null || ! in_array(strtolower($usuario->perfil), array_map('strtolower', $perfis), true)) {
            abort(403, 'Você não tem permissão para esta área.');
        }

        return $next($request);
    }
}
