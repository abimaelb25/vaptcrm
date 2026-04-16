<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('painel')->group(function () {
    Route::redirect('comercial/pedidos', '/painel/vendas/pedidos', 301);
    Route::redirect('comercial/clientes', '/painel/vendas/clientes', 301);
    Route::redirect('operacao/produtos', '/painel/catalogo/produtos', 301);
    Route::redirect('operacao/funcionarios', '/painel/sistema/equipe', 301);
    Route::redirect('financeiro', '/painel/gestao-financeira', 301);
    Route::redirect('configuracoes/site', '/painel/sistema/configuracoes', 301);
});
