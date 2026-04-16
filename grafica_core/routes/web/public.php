<?php

declare(strict_types=1);

use App\Http\Controllers\Catalogo\CatalogoController;
use App\Http\Controllers\Catalogo\CheckoutController;
use App\Http\Controllers\Catalogo\ConsultaPedidoController;
use App\Http\Controllers\Financeiro\StripeController;
use App\Http\Controllers\SaaS\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogoController::class, 'inicio'])->name('site.inicio');
Route::get('/catalogo', [CatalogoController::class, 'catalogo'])->name('site.catalogo');
Route::get('/catalogo/categoria/{slug}', [CatalogoController::class, 'categoriaLista'])->name('site.categoria');
Route::get('/produto/{produto:slug}', [CatalogoController::class, 'produto'])->name('site.produto');

Route::prefix('checkout')->name('site.checkout.')->group(function () {
    Route::post('/{produto:slug}', [CheckoutController::class, 'store'])->name('store');
    Route::get('/sucesso/{numero}', [CheckoutController::class, 'sucesso'])->name('sucesso');
});

Route::get('/acompanhar-pedido', [ConsultaPedidoController::class, 'formulario'])->name('site.pedido.acompanhar');
Route::post('/acompanhar-pedido', [ConsultaPedidoController::class, 'consultar'])->name('site.pedido.consultar');

Route::get('/registrar/{plano?}', [OnboardingController::class, 'show'])->name('onboarding.start');
Route::post('/registrar', [OnboardingController::class, 'store'])->name('onboarding.store');

Route::get('/pagamentos/stripe/sucesso', [StripeController::class, 'sucesso'])->name('stripe.sucesso');
Route::get('/pagamentos/stripe/cancelado', [StripeController::class, 'cancelado'])->name('stripe.cancelado');

Route::get('/p/{slug}', function ($slug) {
    $pagina = \App\Models\PaginaLegal::query()
        ->where('slug', $slug)
        ->where('ativa', true)
        ->firstOrFail();

    $renderService = app(\App\Services\Domain\LegalPageRenderService::class);
    $pagina->conteudo = $renderService->render($pagina);

    return view('publico.pagina-legal', compact('pagina'));
})->name('site.pagina');
