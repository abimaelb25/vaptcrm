<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Agendamento de tarefas automáticas do sistema.
| Autoria: Abimael Borges | https://abimaelborges.adv.br | 17/04/2026
|
| Para ativar em produção, adicionar no crontab do servidor:
| * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
*/

// Verifica pedidos atrasados a cada hora e notifica equipe via CRM interno (sino)
Schedule::job(new \App\Jobs\VerificarPedidosAtrasadosJob)->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Consolidação de uso por ciclo para preparo de cobrança SaaS.
Schedule::job(new \App\Jobs\ConsolidarUsageCicloJob)->dailyAt('01:20')
    ->withoutOverlapping()
    ->onOneServer();

// Governação de expiração/grace period com bloqueio automático da loja.
Schedule::job(new \App\Jobs\ProcessarExpiracaoPlanosJob)->hourly()
    ->withoutOverlapping()
    ->onOneServer();
