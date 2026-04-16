<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SessaoController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [SessaoController::class, 'formulario'])->name('login');
    Route::post('/entrar', [SessaoController::class, 'autenticar'])->name('auth.autenticar');

    Route::get('/recuperar-senha', [SessaoController::class, 'recuperarSenhaForm'])->name('password.request');
    Route::post('/recuperar-senha', [SessaoController::class, 'enviarRecuperacao'])->name('password.email');
});

Route::match(['GET', 'POST'], '/sair', [SessaoController::class, 'sair'])
    ->middleware('auth')
    ->name('auth.sair');
