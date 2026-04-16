<?php

declare(strict_types=1);

use App\Http\Controllers\SuperAdmin\AssinaturasController as SuperAdminAssinaturasController;
use App\Http\Controllers\SuperAdmin\BrandingController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\DepoimentoSoftwareController;
use App\Http\Controllers\SuperAdmin\LojasController as SuperAdminLojasController;
use App\Http\Controllers\SuperAdmin\PlanosController as SuperAdminPlanosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'super_admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('lojas', SuperAdminLojasController::class)->only(['index', 'show']);
        Route::post('lojas/{loja}/bloquear', [SuperAdminLojasController::class, 'bloquear'])->name('lojas.bloquear');
        Route::post('lojas/{loja}/desbloquear', [SuperAdminLojasController::class, 'desbloquear'])->name('lojas.desbloquear');
        Route::resource('planos', SuperAdminPlanosController::class);
        Route::resource('assinaturas', SuperAdminAssinaturasController::class)->only(['index', 'show']);

        Route::resource('depoimentos', DepoimentoSoftwareController::class);
        Route::get('branding', [BrandingController::class, 'index'])->name('branding.index');
        Route::post('branding', [BrandingController::class, 'update'])->name('branding.update');

        Route::prefix('suporte')->name('support.')->group(function () {
            Route::resource('categorias', \App\Http\Controllers\SuperAdmin\Support\SupportCategoryController::class);
            Route::resource('tickets', \App\Http\Controllers\SuperAdmin\Support\SupportTicketController::class);
            Route::post('tickets/{ticket}/reply', [\App\Http\Controllers\SuperAdmin\Support\SupportTicketController::class, 'reply'])->name('tickets.reply');
            Route::resource('central-de-ajuda', \App\Http\Controllers\SuperAdmin\Support\HelpContentController::class)->parameters([
                'central-de-ajuda' => 'help_content',
            ]);
        });
    });
