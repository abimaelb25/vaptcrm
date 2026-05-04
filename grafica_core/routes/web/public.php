<?php

declare(strict_types=1);

use App\Http\Controllers\Catalogo\CatalogoController;
use App\Http\Controllers\Catalogo\CartController;
use App\Http\Controllers\Catalogo\CheckoutController;
use App\Http\Controllers\Catalogo\ConsultaPedidoController;
use App\Http\Controllers\Financeiro\StripeController;
use App\Http\Controllers\Site\LegalPageController;
use App\Http\Controllers\SaaS\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogoController::class, 'inicio'])->name('site.inicio');
Route::get('/catalogo', [CatalogoController::class, 'catalogo'])->name('site.catalogo');

// Redirect permanente da rota antiga de categoria para query string (compatibilidade)
Route::get('/catalogo/categoria/{slug}', function (string $slug) {
    return redirect()->route('site.catalogo', ['categoria' => $slug], 301);
})->name('site.categoria');
Route::get('/produto/{produto:slug}', [CatalogoController::class, 'produto'])->name('site.produto');

// Carrinho de Compras
Route::prefix('carrinho')->name('site.carrinho')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/adicionar', [CartController::class, 'adicionar'])->name('.adicionar');
    Route::patch('/atualizar/{itemKey}', [CartController::class, 'atualizar'])->name('.atualizar');
    Route::delete('/remover/{itemKey}', [CartController::class, 'remover'])->name('.remover');
    Route::delete('/limpar', [CartController::class, 'limpar'])->name('.limpar');
    Route::get('/resumo', [CartController::class, 'resumo'])->name('.resumo');
});

Route::prefix('checkout')->name('site.checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('carrinho');   // Checkout do carrinho (ETAPA 6)
    Route::post('/finalizar', [CheckoutController::class, 'finalizarCarrinho'])->name('finalizar'); // ETAPA 6
    Route::post('/{produto:slug}', [CheckoutController::class, 'store'])->name('store'); // Legado: produto único
    Route::get('/sucesso/{numero}', [CheckoutController::class, 'sucesso'])->name('sucesso');
});

Route::get('/acompanhar-pedido', [ConsultaPedidoController::class, 'formulario'])->name('site.pedido.acompanhar');
Route::post('/acompanhar-pedido', [ConsultaPedidoController::class, 'consultar'])->name('site.pedido.consultar');

Route::get('/registrar/{plano?}', [OnboardingController::class, 'show'])->name('onboarding.start');
Route::post('/registrar', [OnboardingController::class, 'store'])->name('onboarding.store');

Route::get('/pagamentos/stripe/sucesso', [StripeController::class, 'sucesso'])->name('stripe.sucesso');
Route::get('/pagamentos/stripe/cancelado', [StripeController::class, 'cancelado'])->name('stripe.cancelado');

Route::get('/p/{slug}', [LegalPageController::class, 'show'])->name('site.pagina');
