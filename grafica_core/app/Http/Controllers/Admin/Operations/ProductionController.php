<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Operations;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:55
*/

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderStep;
use App\Models\ProductionStep;
use App\Models\Usuario;
use App\Services\Domain\ProductionService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function __construct(
        protected ProductionService $productionService,
        protected FinancePlanService $planService
    ) {}

    /**
     * Dashboard de Produção (Chão de Fábrica).
     */
    public function index(Request $request): View
    {
        $query = ProductionOrder::with(['pedido.cliente', 'responsavel', 'stages.stepDefinition']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'finalizado');
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        return view('painel.operacoes.producao.index', [
            'orders' => $query->latest()->paginate(20),
            'usuarios' => Usuario::where('ativo', true)->get(),
        ]);
    }

    /**
     * Detalhes de uma Ordem de Produção.
     */
    public function show(ProductionOrder $productionOrder): View
    {
        $productionOrder->load(['stages.stepDefinition', 'stages.responsavel', 'pedido.itens.produto']);
        
        return view('painel.operacoes.producao.show', [
            'order' => $productionOrder,
            'usuarios' => Usuario::where('ativo', true)->get(),
        ]);
    }

    /**
     * Atualiza o status de uma etapa.
     */
    public function updateStep(Request $request, ProductionOrderStep $step): RedirectResponse
    {
        if ($request->status === 'em_andamento' && !$this->planService->canUsePro()) {
             // Etapas detalhadas podem ser restritas no futuro se necessário
        }

        $this->productionService->updateStepStatus($step, $request->status, auth()->id());

        return back()->with('sucesso', 'Etapa de produção atualizada.');
    }

    /**
     * Configuração de Etapas Padrão.
     */
    public function settings(): View
    {
        if (!$this->planService->canUsePremium()) {
            return view('painel.billing.upgrade_needed', ['feature' => 'Fluxo de Produção Personalizado']);
        }

        return view('painel.operacoes.producao.settings', [
            'steps' => ProductionStep::orderBy('ordem')->get(),
        ]);
    }

    public function storeStep(Request $request): RedirectResponse
    {
        $request->validate(['nome' => 'required|string|max:255']);
        
        ProductionStep::create([
            'loja_id' => auth()->user()->loja_id,
            'nome' => $request->nome,
            'ordem' => ProductionStep::count() + 1,
        ]);

        return back()->with('sucesso', 'Etapa de produção adicionada.');
    }
}
