<?php

declare(strict_types=1);

namespace App\Http;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 20:11 -03:00
*/

use App\Http\Middleware\VerificarPerfil;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        'perfil' => VerificarPerfil::class,
    ];
}
