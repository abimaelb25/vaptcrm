<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Catalog\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController as AdminProductController;
use App\Http\Controllers\Admin\Finance\FinancialTitleController;
use App\Http\Controllers\Admin\Finance\PagamentosController as AdminPagamentosController;
use App\Http\Controllers\Admin\Finance\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\Inventory\AssetController;
use App\Http\Controllers\Admin\Inventory\EstoqueMovimentacaoController;
use App\Http\Controllers\Admin\Inventory\FornecedorController;
use App\Http\Controllers\Admin\Inventory\InsumoController;
use App\Http\Controllers\Admin\Operations\ProductionController;
use App\Http\Controllers\Admin\POS\PDVController;
use App\Http\Controllers\Admin\Relatorios\CaixaController as AdminCaixaRelatorioController;
use App\Http\Controllers\Admin\Sales\CupomController as AdminCupomController;
use App\Http\Controllers\Admin\Sales\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\Sales\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\System\AparenciaController as AdminAparenciaController;
use App\Http\Controllers\Admin\System\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\System\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\CMS\BannerController;
use App\Http\Controllers\CMS\DepoimentoController;
use App\Http\Controllers\CMS\PaginaLegalController;
use App\Http\Controllers\Comercial\ContatoController;
use App\Http\Controllers\Conta\PerfilController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RelatorioController;
use App\Http\Controllers\Financeiro\StripeController;
use App\Http\Controllers\Operacao\TarefaController;
use App\Http\Controllers\SaaS\AssinaturaController;
use App\Http\Controllers\Catalogo\PrecificadorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'perfil:super_admin,administrador,gerente,atendente,produção,financeiro', 'assinatura'])
    ->prefix('painel')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::prefix('catalogo')->name('catalog.')->group(function () {
            Route::resource('produtos', AdminProductController::class);
            Route::patch('produtos/{produto}/toggle', [AdminProductController::class, 'toggleAtivo'])->name('produtos.toggle');
            Route::post('produtos/{produto}/duplicate', [AdminProductController::class, 'duplicate'])->name('produtos.duplicate');

            Route::resource('categorias', AdminCategoryController::class);
            Route::post('categorias/ordenar', [AdminCategoryController::class, 'ordenar'])->name('categorias.ordenar');

            Route::prefix('precificacao')->name('pricing.')->group(function () {
                Route::get('/', [PrecificadorController::class, 'index'])->name('index');
                Route::post('/salvar-produto', [PrecificadorController::class, 'saveToProduct'])->name('save_product');
                Route::post('/salvar-orcamento', [PrecificadorController::class, 'linkToOrder'])->name('save_order');
            });
        });

        Route::prefix('vendas')->name('sales.')->group(function () {
            Route::patch('pedidos/kanban-status', [AdminOrderController::class, 'updateKanbanStatus'])->name('pedidos.kanban-status');
            Route::patch('pedidos/update-kanban-status', [AdminOrderController::class, 'updateKanbanStatus'])->name('pedidos.update-kanban-status');
            Route::resource('pedidos', AdminOrderController::class);
            Route::patch('pedidos/{pedido}/status', [AdminOrderController::class, 'updateStatus'])->name('pedidos.status');
            Route::patch('pedidos/{pedido}/marcar-pago', [AdminOrderController::class, 'marcarPago'])->name('pedidos.marcar-pago');
            Route::get('pedidos/arte/{item}', [AdminOrderController::class, 'downloadArte'])->name('pedidos.arte');
            Route::patch('pedidos/{pedido}/converter-orcamento', [AdminOrderController::class, 'updateStatus'])->name('pedidos.converter-orcamento');
            Route::patch('pedidos/{pedido}/encaminhar', [AdminOrderController::class, 'updateStatus'])->name('pedidos.encaminhar');

            Route::resource('clientes', AdminCustomerController::class);
            Route::resource('cupons', AdminCupomController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('cupons/{cupom}/toggle', [AdminCupomController::class, 'toggle'])->name('cupons.toggle');
            Route::post('contatos', [ContatoController::class, 'store'])->name('contatos.store');
        });

        Route::prefix('pdv')->name('pos.')->group(function () {
            Route::get('/', [PDVController::class, 'index'])->name('index');
            Route::get('/clientes', [PDVController::class, 'buscarClientes'])->name('clientes');
            Route::get('/produtos', [PDVController::class, 'buscarProdutos'])->name('produtos');
            Route::post('/cliente-rapido', [PDVController::class, 'clienteRapido'])->name('cliente-rapido');
            Route::post('/produto-rapido', [PDVController::class, 'produtoRapido'])->name('produto-rapido');
            Route::post('/finalizar', [PDVController::class, 'finalizar'])->name('finalizar');
            Route::get('/os/{pedido}', [PDVController::class, 'gerarOS'])->name('os');

            Route::middleware(['perfil:administrador,gerente'])->group(function () {
                Route::get('/caixa/status', [PDVController::class, 'statusCaixa'])->name('caixa.status');
                Route::post('/caixa/abrir', [PDVController::class, 'abrirCaixa'])->name('caixa.abrir');
                Route::post('/caixa/fechar', [PDVController::class, 'fecharCaixa'])->name('caixa.fechar');
            });
        });

        Route::middleware(['perfil:administrador,gerente,financeiro'])->prefix('gestao-financeira')->name('finance.')->group(function () {
            Route::get('/', [FinancialTitleController::class, 'dashboard'])->name('index');
            Route::get('/receber', [FinancialTitleController::class, 'receivable'])->name('receivable');
            Route::get('/pagar', [FinancialTitleController::class, 'payable'])->name('payable');
            Route::post('/titulos', [FinancialTitleController::class, 'store'])->name('titles.store');
            Route::post('/titulos/{title}/pagar', [FinancialTitleController::class, 'pay'])->name('titles.pay');

            Route::get('/movimentacoes', [AdminTransactionController::class, 'index'])->name('transactions.index');
            Route::get('/extrato', [AdminTransactionController::class, 'extrato'])->name('extrato');
            Route::post('/lancar', [AdminTransactionController::class, 'store'])->name('store');
            Route::delete('/{movimentacao}', [AdminTransactionController::class, 'destroy'])->name('destroy');

            Route::post('checkout/online/{pedido}', [StripeController::class, 'checkoutOnline'])->name('stripe.online');
            Route::post('checkout/presencial/{pedido}', [StripeController::class, 'checkoutPresencial'])->name('stripe.presencial');

            Route::prefix('pagamentos')->name('pagamentos.')->group(function () {
                Route::get('/', [AdminPagamentosController::class, 'index'])->name('index');
                Route::put('/frete', [AdminPagamentosController::class, 'updateFrete'])->name('frete');
                Route::put('/pix', [AdminPagamentosController::class, 'updatePix'])->name('pix');
                Route::put('/mercado-pago', [AdminPagamentosController::class, 'updateMercadoPago'])->name('mercado-pago');
                Route::get('/mercado-pago/testar', [AdminPagamentosController::class, 'testarMercadoPago'])->name('mercado-pago.testar');
            });
        });

        Route::prefix('sistema')->name('system.')->group(function () {
            Route::get('configuracoes', [AdminConfigController::class, 'index'])->name('config.index');
            Route::post('configuracoes', [AdminConfigController::class, 'update'])->name('config.update');
            Route::get('configuracoes/exportar', [AdminConfigController::class, 'export'])->name('config.export');
            Route::post('configuracoes/importar', [AdminConfigController::class, 'import'])->name('config.import');
            Route::resource('equipe', AdminEmployeeController::class);
            Route::post('equipe/solicitacao/{solicitacao}/aprovar', [AdminEmployeeController::class, 'aprovarSolicitacao'])->name('equipe.aprovar-solicitacao');
            Route::post('equipe/solicitacao/{solicitacao}/rejeitar', [AdminEmployeeController::class, 'rejeitarSolicitacao'])->name('equipe.rejeitar-solicitacao');
            Route::get('aparencia', [AdminAparenciaController::class, 'index'])->name('aparencia.index');
            Route::post('aparencia', [AdminAparenciaController::class, 'update'])->name('aparencia.update');
            Route::resource('banners', BannerController::class);
            Route::resource('depoimentos', DepoimentoController::class);
            Route::resource('paginas-legais', PaginaLegalController::class)->parameters(['paginas-legais' => 'pagina']);
        });

        Route::prefix('estoque')->name('inventory.')->group(function () {
            Route::get('/alertas', [InsumoController::class, 'alertas'])->name('insumos.alertas');
            Route::resource('insumos', InsumoController::class);
            Route::resource('fornecedores', FornecedorController::class);
            Route::get('/movimentacoes', [EstoqueMovimentacaoController::class, 'index'])->name('movimentacoes.index');
            Route::get('/movimentacoes/entrada', [EstoqueMovimentacaoController::class, 'entrada'])->name('movimentacoes.entrada');
            Route::post('/movimentacoes/entrada', [EstoqueMovimentacaoController::class, 'processarEntrada'])->name('movimentacoes.processar-entrada');
            Route::get('/movimentacoes/saida', [EstoqueMovimentacaoController::class, 'saida'])->name('movimentacoes.saida');
            Route::post('/movimentacoes/saida', [EstoqueMovimentacaoController::class, 'processarSaida'])->name('movimentacoes.processar-saida');
            Route::get('/insumos/{insumo}/ajuste', [EstoqueMovimentacaoController::class, 'ajuste'])->name('insumos.ajuste');
            Route::post('/insumos/{insumo}/ajuste', [EstoqueMovimentacaoController::class, 'processarAjuste'])->name('insumos.processar-ajuste');
            Route::resource('assets', AssetController::class);
            Route::post('assets/{asset}/maintenances', [AssetController::class, 'storeMaintenance'])->name('assets.maintenances.store');
        });

        Route::prefix('producao')->name('ops.')->group(function () {
            Route::get('quadro-geral', [TarefaController::class, 'quadroGeral'])->name('tasks.board');
            Route::post('tarefas', [TarefaController::class, 'store'])->name('tasks.store');
            Route::patch('tarefas/{tarefa}/status', [TarefaController::class, 'atualizarStatus'])->name('tasks.status');
            Route::delete('tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tasks.destroy');
            Route::get('chao-de-fabrica', [ProductionController::class, 'index'])->name('production.index');
            Route::get('chao-de-fabrica/{production_order}', [ProductionController::class, 'show'])->name('production.show');
            Route::post('chao-de-fabrica/steps/{step}/update', [ProductionController::class, 'updateStep'])->name('production.step.update');
            Route::get('configuracoes', [ProductionController::class, 'settings'])->name('production.settings');
            Route::post('configuracoes/steps', [ProductionController::class, 'storeStep'])->name('production.step.store');
        });

        Route::prefix('bi')->name('bi.')->group(function () {
            Route::get('/', [RelatorioController::class, 'index'])->name('index');
            Route::get('/exportar-pedidos', [RelatorioController::class, 'exportarPedidos'])->name('export.orders');

            Route::middleware(['perfil:administrador,gerente,financeiro'])->group(function () {
                Route::get('/caixas', [AdminCaixaRelatorioController::class, 'index'])->name('caixas.index');
                Route::get('/caixas/{caixa}', [AdminCaixaRelatorioController::class, 'show'])->name('caixas.show');
                Route::post('/caixas/{caixa}/fechar', [AdminCaixaRelatorioController::class, 'fechar'])->name('caixas.fechar');
            });
        });

        Route::prefix('assinatura')->name('billing.')->group(function () {
            Route::get('/', [AssinaturaController::class, 'index'])->name('index');
            Route::get('/assinar/{plano}', [AssinaturaController::class, 'assinar'])->name('subscribe');
            Route::get('/portal', [AssinaturaController::class, 'portal'])->name('portal');
        });

        Route::prefix('suporte')->name('support.')->group(function () {
            Route::resource('meus-tickets', \App\Http\Controllers\Admin\Support\StoreTicketController::class)->parameters([
                'meus-tickets' => 'ticket',
            ]);
            Route::post('meus-tickets/{ticket}/reply', [\App\Http\Controllers\Admin\Support\StoreTicketController::class, 'reply'])->name('meus-tickets.reply');
            Route::get('central-de-ajuda', [\App\Http\Controllers\Admin\Support\HelpCenterController::class, 'index'])->name('help.index');
            Route::get('central-de-ajuda/{help_content}', [\App\Http\Controllers\Admin\Support\HelpCenterController::class, 'show'])->name('help.show');
        });

        Route::prefix('perfil')->name('profile.')->group(function () {
            Route::post('/atualizar', [PerfilController::class, 'atualizarSenhaAvatar'])->name('update');
            Route::post('/solicitar-dados', [PerfilController::class, 'solicitarAtualizacao'])->name('request-update');
            Route::post('/upload-docs', [PerfilController::class, 'uploadDocumento'])->name('docs');
            Route::get('/download/{documento}', [PerfilController::class, 'visualizarDocumento'])->name('docs.download');
        });
    });

Route::get('/loja-bloqueada', function () {
    if (!Auth::check() || !Auth::user()->loja || !Auth::user()->loja->estaBloqueada()) {
        return redirect()->route('admin.dashboard');
    }

    return view('errors.loja-bloqueada');
})->middleware(['auth'])->name('admin.loja.bloqueada');
