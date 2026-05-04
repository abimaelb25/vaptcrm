<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-04 19:45 -03:00
*/

use App\Http\Controllers\Api\WebhookAsaasController;
use App\Http\Controllers\Api\SaaS\BillingWebhookController;
use App\Http\Controllers\Financeiro\StripeController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/asaas', WebhookAsaasController::class)->name('api.webhooks.asaas');

// Webhook Stripe — sem CSRF (excluído em bootstrap/app.php), com verificação HMAC interna
Route::post('/webhooks/stripe', [StripeController::class, 'webhook'])->name('api.webhooks.stripe');
Route::post('/webhooks/saas/stripe', [BillingWebhookController::class, 'stripe'])->name('api.webhooks.saas.stripe');
Route::post('/webhooks/saas/mercadopago', [BillingWebhookController::class, 'mercadoPago'])->name('api.webhooks.saas.mercadopago');

// WhatsApp Business Platform webhooks (Meta Cloud API)
// Verification (GET) and event ingestion (POST) — CSRF exempt (see bootstrap/app.php)
Route::get('/webhooks/whatsapp/{account}', [\App\Http\Controllers\Admin\WhatsApp\WhatsAppWebhookController::class, 'verify'])->name('api.webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp/{account}', [\App\Http\Controllers\Admin\WhatsApp\WhatsAppWebhookController::class, 'receive'])->name('api.webhooks.whatsapp.receive');
