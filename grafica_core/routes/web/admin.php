<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Catalog\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController as AdminProductController;
use App\Http\Controllers\Admin\Finance\FinancialAccountController;
use App\Http\Controllers\Admin\Finance\FinancialTitleController;
use App\Http\Controllers\Admin\Finance\PagamentosController as AdminPagamentosController;
use App\Http\Controllers\Admin\Finance\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\Inventory\AssetController;
use App\Http\Controllers\Admin\Inventory\EstoqueMovimentacaoController;
use App\Http\Controllers\Admin\Inventory\FornecedorController;
use App\Http\Controllers\Admin\Inventory\InsumoController;
use App\Http\Controllers\Admin\Inventory\NfeImportacaoController;
use App\Http\Controllers\Admin\Operations\ProductionKanbanController;
use App\Http\Controllers\Admin\Operations\ProductionController;
use App\Http\Controllers\Admin\POS\PDVController;
use App\Http\Controllers\Admin\Relatorios\CaixaController as AdminCaixaRelatorioController;
use App\Http\Controllers\Admin\Sales\CupomController as AdminCupomController;
use App\Http\Controllers\Admin\Sales\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\Sales\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\System\AparenciaController as AdminAparenciaController;
use App\Http\Controllers\Admin\WhatsApp\WhatsAppCampaignController;
use App\Http\Controllers\Admin\WhatsApp\WhatsAppInboxController;
use App\Http\Controllers\Admin\WhatsApp\WhatsAppOnboardingController;
use App\Http\Controllers\Admin\WhatsApp\WhatsAppOperationsController;
use App\Http\Controllers\Admin\System\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\System\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\System\EmployeeOccurrenceController as AdminEmployeeOccurrenceController;
use App\Http\Controllers\Admin\System\EmployeeOccurrenceAttachmentController as AdminEmployeeOccurrenceAttachmentController;
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
use App\Http\Controllers\Api\ProductionOrderApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'perfil:super_admin,administrador,gerente,atendente,produção,financeiro', 'assinatura'])
    ->prefix('painel')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::prefix('catalogo')->name('catalog.')->middleware(['check_plan_feature:modulo_produtos'])->group(function () {
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

            // Engine Dinâmica de Produtos (Nova Estrutura)
            Route::prefix('produtos/{produto}/pricing')->name('produtos.pricing.')->group(function () {
                Route::post('/simular', [\App\Http\Controllers\Admin\Catalog\ProductPricingController::class, 'simular'])->name('simular');
                Route::post('/recalcular', [\App\Http\Controllers\Admin\Catalog\ProductPricingController::class, 'recalcular'])->name('recalcular');
                Route::get('/resumo', [\App\Http\Controllers\Admin\Catalog\ProductPricingController::class, 'resumo'])->name('resumo');
            });
        });

        Route::prefix('vendas')->name('sales.')->middleware(['check_plan_feature:modulo_pedidos'])->group(function () {
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

        Route::prefix('pdv')->name('pos.')->middleware(['check_plan_feature:modulo_pedidos'])->group(function () {
            Route::get('/', [PDVController::class, 'index'])->name('index');
            Route::get('/clientes', [PDVController::class, 'buscarClientes'])->name('clientes');
            Route::get('/produtos', [PDVController::class, 'buscarProdutos'])->name('produtos');
            Route::get('/pedidos', [PDVController::class, 'buscarPedidos'])->name('pedidos');
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

        Route::middleware(['perfil:administrador,gerente,financeiro', 'check_plan_feature:modulo_financeiro'])->prefix('gestao-financeira')->name('finance.')->group(function () {
            Route::get('/', [FinancialTitleController::class, 'dashboard'])->name('index');
            Route::get('/receber', [FinancialTitleController::class, 'receivable'])->name('receivable');
            Route::get('/pagar', [FinancialTitleController::class, 'payable'])->name('payable');
            Route::post('/titulos', [FinancialTitleController::class, 'store'])->name('titles.store');
            Route::post('/titulos/{title}/pagar', [FinancialTitleController::class, 'pay'])->name('titles.pay');

            // Contas Bancárias (CRUD)
            Route::get('/contas', [FinancialAccountController::class, 'index'])->name('accounts.index');
            Route::post('/contas', [FinancialAccountController::class, 'store'])->name('accounts.store');
            Route::put('/contas/{conta}', [FinancialAccountController::class, 'update'])->name('accounts.update');
            Route::delete('/contas/{conta}', [FinancialAccountController::class, 'destroy'])->name('accounts.destroy');
            Route::patch('/contas/{conta}/toggle', [FinancialAccountController::class, 'toggle'])->name('accounts.toggle');

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
            
            // Equipe & Colaboradores (aninhado com Ocorrências RH e Anexos)
            Route::resource('equipe', AdminEmployeeController::class);
            Route::get('equipe-treinamentos', [\App\Http\Controllers\Admin\System\EmployeeTrainingController::class, 'index'])
                ->name('equipe.treinamentos.index');
            Route::get('equipe/{equipe}/treinamentos', [\App\Http\Controllers\Admin\System\EmployeeTrainingController::class, 'show'])
                ->name('equipe.treinamentos.show');
            Route::resource('equipe.ocorrencias', AdminEmployeeOccurrenceController::class)
                ->parameters(['equipe' => 'equipe', 'ocorrencias' => 'ocorrencia']);
            
            // Anexos de Ocorrências (POST para upload, DELETE para remover, GET para download)
            Route::post('equipe/{equipe}/ocorrencias/{ocorrencia}/anexos', 
                [AdminEmployeeOccurrenceAttachmentController::class, 'store'])
                ->name('system.equipe.ocorrencias.anexos.store');
            Route::delete('equipe/{equipe}/ocorrencias/{ocorrencia}/anexos/{anexo}',
                [AdminEmployeeOccurrenceAttachmentController::class, 'destroy'])
                ->name('system.equipe.ocorrencias.anexos.destroy');
            Route::get('equipe/{equipe}/ocorrencias/{ocorrencia}/anexos/{anexo}/download',
                [AdminEmployeeOccurrenceAttachmentController::class, 'download'])
                ->name('system.equipe.ocorrencias.anexos.download');
            
            Route::get('aparencia', [AdminAparenciaController::class, 'index'])->name('aparencia.index');
            Route::post('aparencia', [AdminAparenciaController::class, 'update'])->name('aparencia.update');
            Route::resource('banners', BannerController::class);
            Route::resource('depoimentos', DepoimentoController::class);
            Route::resource('paginas-legais', PaginaLegalController::class)->parameters(['paginas-legais' => 'pagina']);
        });

        Route::prefix('estoque')->name('inventory.')->middleware(['check_plan_feature:modulo_estoque'])->group(function () {
            Route::get('/alertas', [InsumoController::class, 'alertas'])->name('insumos.alertas');
            Route::resource('insumos', InsumoController::class);
            Route::resource('fornecedores', FornecedorController::class);
            Route::get('/movimentacoes', [EstoqueMovimentacaoController::class, 'index'])->name('movimentacoes.index');
            Route::get('/movimentacoes/entrada', [EstoqueMovimentacaoController::class, 'entrada'])->name('movimentacoes.entrada');
            Route::post('/movimentacoes/entrada', [EstoqueMovimentacaoController::class, 'processarEntrada'])->name('movimentacoes.processar-entrada');
            Route::get('/movimentacoes/saida', [EstoqueMovimentacaoController::class, 'saida'])->name('movimentacoes.saida');
            Route::post('/movimentacoes/saida', [EstoqueMovimentacaoController::class, 'processarSaida'])->name('movimentacoes.processar-saida');
            Route::get('/nfe/importar', [NfeImportacaoController::class, 'create'])->name('nfe-importacao.create');
            Route::post('/nfe/importar', [NfeImportacaoController::class, 'preview'])->name('nfe-importacao.preview');
            Route::get('/nfe/importacoes', [NfeImportacaoController::class, 'index'])->name('nfe-importacao.index');
            Route::get('/nfe/importacoes/{nfeImportacao}', [NfeImportacaoController::class, 'show'])->name('nfe-importacao.show');
            Route::post('/nfe/importacoes/{nfeImportacao}/confirmar', [NfeImportacaoController::class, 'confirmar'])->name('nfe-importacao.confirmar');
            Route::post('/nfe/importacoes/{nfeImportacao}/reabrir', [NfeImportacaoController::class, 'reabrir'])->name('nfe-importacao.reabrir');
            Route::get('/insumos/{insumo}/ajuste', [EstoqueMovimentacaoController::class, 'ajuste'])->name('insumos.ajuste');
            Route::post('/insumos/{insumo}/ajuste', [EstoqueMovimentacaoController::class, 'processarAjuste'])->name('insumos.processar-ajuste');
            Route::post('/insumos/{insumo}/inativar', [InsumoController::class, 'inativar'])->name('insumos.inativar');
            Route::resource('assets', AssetController::class);
            Route::post('assets/{asset}/maintenances', [AssetController::class, 'storeMaintenance'])->name('assets.maintenances.store');
        });

        Route::prefix('producao')->name('ops.')->middleware(['check_plan_feature:modulo_producao'])->group(function () {
            Route::get('quadro-geral', [TarefaController::class, 'quadroGeral'])->name('tasks.board');
            Route::post('tarefas', [TarefaController::class, 'store'])->name('tasks.store');
            Route::patch('tarefas/{tarefa}/status', [TarefaController::class, 'atualizarStatus'])->name('tasks.status');
            Route::delete('tarefas/{tarefa}', [TarefaController::class, 'destroy'])->name('tasks.destroy');
            Route::get('kanban', [ProductionKanbanController::class, 'index'])->middleware(['check_plan_feature:modulo_kanban'])->name('production.kanban');
            Route::get('chao-de-fabrica', [ProductionController::class, 'index'])->name('production.index');
            Route::get('chao-de-fabrica/{production_order}', [ProductionController::class, 'show'])->name('production.show');
            Route::post('chao-de-fabrica/steps/{step}/update', [ProductionController::class, 'updateStep'])->name('production.step.update');
            Route::get('configuracoes', [ProductionController::class, 'settings'])->name('production.settings');
            Route::post('configuracoes/steps', [ProductionController::class, 'storeStep'])->name('production.step.store');
            Route::post('configuracoes/fases/{phase}/steps', [ProductionController::class, 'storeStepInPhase'])->name('production.phase.step.store');
            Route::post('configuracoes/steps/{step}/move', [ProductionController::class, 'moveStep'])->name('production.step.move');
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
            Route::get('/preview-downgrade/{plano}', [AssinaturaController::class, 'previewDowngrade'])->name('preview-downgrade');
            Route::post('/change-plan/{plano}', [AssinaturaController::class, 'changePlan'])->name('change-plan');
        });

        Route::prefix('suporte')->name('support.')->group(function () {
            Route::resource('meus-tickets', \App\Http\Controllers\Admin\Support\StoreTicketController::class)->parameters([
                'meus-tickets' => 'ticket',
            ]);
            Route::post('meus-tickets/{ticket}/reply', [\App\Http\Controllers\Admin\Support\StoreTicketController::class, 'reply'])->name('meus-tickets.reply');
            Route::get('central-de-ajuda', [\App\Http\Controllers\Admin\Support\HelpCenterController::class, 'index'])->name('help.index');
            Route::get('central-de-ajuda/{help_content}', [\App\Http\Controllers\Admin\Support\HelpCenterController::class, 'show'])->name('help.show');
            Route::get('central-de-ajuda/{help_content}/quiz', [\App\Http\Controllers\Admin\Support\LessonQuizController::class, 'show'])->name('help.quiz.show');
            Route::post('central-de-ajuda/{help_content}/quiz/responder', [\App\Http\Controllers\Admin\Support\LessonQuizController::class, 'answer'])->name('help.quiz.answer');
            Route::get('central-de-ajuda/{help_content}/quiz/resultado', [\App\Http\Controllers\Admin\Support\LessonQuizController::class, 'result'])->name('help.quiz.result');
            Route::post('central-de-ajuda/{help_content}/quiz/refazer', [\App\Http\Controllers\Admin\Support\LessonQuizController::class, 'retry'])->name('help.quiz.retry');
            Route::post('central-de-ajuda/{help_content}/concluir', [\App\Http\Controllers\Admin\Support\HelpCenterController::class, 'concluir'])
                ->name('help.complete');
        });

        Route::prefix('perfil')->name('profile.')->group(function () {
            Route::post('/atualizar', [PerfilController::class, 'atualizarSenhaAvatar'])->name('update');
            Route::post('/solicitar-dados', [PerfilController::class, 'solicitarAtualizacao'])->name('request-update');
            Route::post('/upload-docs', [PerfilController::class, 'uploadDocumento'])->middleware(['check_storage_limit'])->name('docs');
            Route::get('/download/{documento}', [PerfilController::class, 'visualizarDocumento'])->name('docs.download');
        });

        // -------------------------------------------------------------------
        // WhatsApp Business Platform
        // -------------------------------------------------------------------
        Route::prefix('whatsapp')->name('whatsapp.')->middleware(['check_plan_feature:modulo_whatsapp'])->group(function () {
            Route::get('/', [WhatsAppOperationsController::class, 'index'])->name('index');
            Route::post('connect', [WhatsAppOperationsController::class, 'connect'])->name('connect');
            Route::post('settings', [WhatsAppOperationsController::class, 'updateSettings'])->name('settings.update');
            Route::post('test-send', [WhatsAppOperationsController::class, 'sendTest'])->name('settings.test-send');
            Route::get('manual-link/{pedido}', [WhatsAppOperationsController::class, 'openManualLink'])->name('manual-link');
            Route::post('accounts/{account}/sync-templates-ui', [WhatsAppOperationsController::class, 'syncTemplates'])->name('accounts.sync-ui');
            Route::get('caixa-de-entrada', [WhatsAppOperationsController::class, 'inbox'])->name('page.inbox');
            Route::get('caixa-de-entrada/{conversation}', [WhatsAppOperationsController::class, 'conversation'])->name('page.conversation');
            Route::post('caixa-de-entrada/{conversation}/send', [WhatsAppOperationsController::class, 'sendConversationMessage'])->name('page.conversation.send');
            Route::get('logs', [WhatsAppOperationsController::class, 'logs'])->name('logs');

            // Dashboard metrics
            Route::get('dashboard', [WhatsAppOperationsController::class, 'dashboard'])->name('dashboard');

            // Campaigns
            Route::get('campanhas', [WhatsAppCampaignController::class, 'index'])->name('campaigns.index');
            Route::get('campanhas/criar', [WhatsAppCampaignController::class, 'create'])->name('campaigns.create');
            Route::post('campanhas', [WhatsAppCampaignController::class, 'store'])->name('campaigns.store');
            Route::get('campanhas/{campaign}', [WhatsAppCampaignController::class, 'show'])->name('campaigns.show');
            Route::post('campanhas/{campaign}/cancelar', [WhatsAppCampaignController::class, 'cancel'])->name('campaigns.cancel');
            Route::post('campanhas/{campaign}/destinatario/{recipient}/enviado', [WhatsAppCampaignController::class, 'markRecipientSent'])->name('campaigns.recipient.sent');
            Route::post('campanhas/preview-destinatarios', [WhatsAppCampaignController::class, 'countPreview'])->name('campaigns.preview');

            // Conversation notes + priority
            Route::post('caixa-de-entrada/{conversation}/notas', [WhatsAppOperationsController::class, 'storeConversationNote'])->name('page.conversation.note.store');
            Route::post('inbox/{conversation}/notas', [WhatsAppOperationsController::class, 'storeConversationNote'])->name('inbox.conversation.note.store');
            Route::patch('inbox/{conversation}/priority', [WhatsAppInboxController::class, 'updatePriority'])->name('inbox.priority');

            // Account management & onboarding
            Route::get('accounts', [WhatsAppOnboardingController::class, 'index'])->name('accounts.index');
            Route::post('accounts/onboard', [WhatsAppOnboardingController::class, 'onboard'])->name('accounts.onboard');
            Route::delete('accounts/{account}', [WhatsAppOnboardingController::class, 'disconnect'])->name('accounts.disconnect');
            Route::get('accounts/{account}/templates', [WhatsAppOnboardingController::class, 'templates'])->name('accounts.templates');
            Route::post('accounts/{account}/templates/sync', [WhatsAppOnboardingController::class, 'syncTemplates'])->name('accounts.templates.sync');
            Route::patch('accounts/{account}/templates/{template}/system-key', [WhatsAppOnboardingController::class, 'setTemplateSystemKey'])->name('accounts.templates.system-key');

            // Inbox
            Route::get('inbox', [WhatsAppInboxController::class, 'index'])->name('inbox.index');
            Route::get('inbox/{conversation}', [WhatsAppInboxController::class, 'show'])->name('inbox.show');
            Route::get('inbox/{conversation}/messages', [WhatsAppInboxController::class, 'messages'])->name('inbox.messages');
            Route::post('inbox/{conversation}/send', [WhatsAppInboxController::class, 'send'])->name('inbox.send');
            Route::patch('inbox/{conversation}/assign', [WhatsAppInboxController::class, 'assign'])->name('inbox.assign');
            Route::patch('inbox/{conversation}/resolve', [WhatsAppInboxController::class, 'resolve'])->name('inbox.resolve');
            Route::patch('inbox/{conversation}/reopen', [WhatsAppInboxController::class, 'reopen'])->name('inbox.reopen');
            Route::patch('inbox/{conversation}/link-cliente', [WhatsAppInboxController::class, 'linkCliente'])->name('inbox.link-cliente');
            Route::patch('inbox/{conversation}/link-pedido', [WhatsAppInboxController::class, 'linkPedido'])->name('inbox.link-pedido');
        });

        Route::prefix('notificacoes')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\System\NotificationController::class, 'index'])->name('index');
            Route::get('/nao-lidas', [\App\Http\Controllers\Admin\System\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::get('/recentes', [\App\Http\Controllers\Admin\System\NotificationController::class, 'recent'])->name('recent');
            Route::post('/lida/{id}', [\App\Http\Controllers\Admin\System\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/lidas-todas', [\App\Http\Controllers\Admin\System\NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\System\NotificationController::class, 'destroy'])->name('destroy');
        });
    });

Route::middleware(['auth', 'perfil:super_admin,administrador,gerente,atendente,produção,financeiro', 'assinatura'])
    ->group(function (): void {
        Route::get('/producao/kanban', [ProductionKanbanController::class, 'index'])
            ->middleware(['check_plan_feature:modulo_kanban'])
            ->name('production.kanban');

        Route::prefix('api/production')->name('api.production.')->middleware(['check_plan_feature:modulo_api', 'check_plan_feature:modulo_producao'])->group(function (): void {
            Route::get('kanban', [ProductionOrderApiController::class, 'kanban']);
            Route::get('metrics', [ProductionOrderApiController::class, 'metrics']);
            Route::post('orders', [ProductionOrderApiController::class, 'store']);
            Route::get('orders/{id}', [ProductionOrderApiController::class, 'show']);
            Route::patch('orders/{id}/move', [ProductionOrderApiController::class, 'move']);
            Route::get('orders/{id}/history', [ProductionOrderApiController::class, 'history']);
            Route::patch('orders/{orderId}/steps/{stepId}/status', [ProductionOrderApiController::class, 'updateStepStatus']);
        });
    });

Route::get('/loja-bloqueada', function () {
    if (!Auth::check() || !Auth::user()->loja || !Auth::user()->loja->estaBloqueada()) {
        return redirect()->route('admin.dashboard');
    }

    return view('errors.loja-bloqueada');
})->middleware(['auth'])->name('admin.loja.bloqueada');
