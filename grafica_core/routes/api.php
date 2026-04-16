<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:45 -03:00
*/

use App\Http\Controllers\Api\WebhookAsaasController;
use App\Http\Controllers\Financeiro\StripeController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/asaas', WebhookAsaasController::class)->name('api.webhooks.asaas');

// Webhook Stripe — sem CSRF (excluído em bootstrap/app.php), com verificação HMAC interna
Route::post('/webhooks/stripe', [StripeController::class, 'webhook'])->name('api.webhooks.stripe');
