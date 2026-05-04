<?php

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use App\Http\Middleware\VerificarPerfil;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TenantDiscovery deve rodar APÓS StartSession para podermos persistir a loja na sessão (útil em dev local/127.0.0.1)
        // No Laravel 12, appendToGroup adiciona ao final do grupo 'web', que já possui a sessão inicializada.
        $middleware->appendToGroup('web', \App\Http\Middleware\SaaS\TenantDiscoveryMiddleware::class);

        $middleware->redirectUsersTo('/painel');
        $middleware->redirectGuestsTo('/entrar');

        $middleware->alias([
            'perfil' => VerificarPerfil::class,
            'assinatura' => \App\Http\Middleware\SaaS\VerificarAssinatura::class,
            'super_admin' => \App\Http\Middleware\SaaS\VerificarSuperAdmin::class,
            'tenant' => \App\Http\Middleware\SaaS\TenantDiscoveryMiddleware::class,
            'check_plan_feature' => \App\Http\Middleware\SaaS\CheckPlanFeature::class,
            'check_plan_limit' => \App\Http\Middleware\SaaS\CheckPlanLimit::class,
            'check_storage_limit' => \App\Http\Middleware\SaaS\CheckStorageLimit::class,
        ]);

        // Exclui CSRF para webhooks externos
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            return back()->with('erro', 'Sessão expirada. Tente novamente.')->withInput();
        });
    })->create();
