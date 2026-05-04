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
use App\Models\ProductionPhase;
use App\Models\ProductionStep;
use App\Models\Usuario;
use App\Services\Domain\ProductionService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        $lojaId = (int) auth()->user()->loja_id;

        return view('painel.operacoes.producao.settings', [
            'phases' => ProductionPhase::where('loja_id', $lojaId)
                ->where('ativo', true)
                ->with(['steps' => function ($query) {
                    $query->where('ativo', true)->orderBy('ordem');
                }])
                ->orderBy('ordem')
                ->get(),
        ]);
    }

    public function storeStep(Request $request): RedirectResponse
    {
        $lojaId = (int) auth()->user()->loja_id;

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'production_phase_id' => [
                'required',
                'integer',
                Rule::exists('production_phases', 'id')->where(function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId)->where('ativo', true);
                }),
            ],
        ]);

        $nextOrder = (int) ProductionStep::withTrashed()
            ->where('loja_id', $lojaId)
            ->where('production_phase_id', (int) $validated['production_phase_id'])
            ->max('ordem') + 1;

        ProductionStep::create([
            'loja_id' => $lojaId,
            'production_phase_id' => (int) $validated['production_phase_id'],
            'nome' => $validated['nome'],
            'ordem' => $nextOrder,
        ]);

        return back()->with('sucesso', 'Etapa de produção adicionada.');
    }

    public function moveStep(Request $request, ProductionStep $step): RedirectResponse
    {
        $lojaId = (int) auth()->user()->loja_id;
        $this->ensureTenantStep($step, $lojaId);

        $validated = $request->validate([
            'direction' => 'nullable|in:up,down',
            'target_phase_id' => [
                'nullable',
                'integer',
                Rule::exists('production_phases', 'id')->where(function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId)->where('ativo', true);
                }),
            ],
        ]);

        $targetPhaseId = isset($validated['target_phase_id']) ? (int) $validated['target_phase_id'] : null;

        if ($targetPhaseId !== null && $targetPhaseId !== (int) $step->production_phase_id) {
            if ($this->wouldLeavePhaseWithoutSteps($lojaId, (int) $step->production_phase_id, (int) $step->id)) {
                return back()->with('erro', 'A fase de origem não pode ficar sem etapas.');
            }

            DB::transaction(function () use ($step, $targetPhaseId, $lojaId): void {
                $oldPhaseId = (int) $step->production_phase_id;

                $nextOrder = (int) ProductionStep::withTrashed()
                    ->where('loja_id', $lojaId)
                    ->where('production_phase_id', $targetPhaseId)
                    ->max('ordem') + 1;

                $step->update([
                    'production_phase_id' => $targetPhaseId,
                    'ordem' => $nextOrder,
                ]);

                $this->normalizePhaseOrder($lojaId, $oldPhaseId);
                $this->normalizePhaseOrder($lojaId, $targetPhaseId);
            });

            return back()->with('sucesso', 'Etapa movida para outra fase.');
        }

        $direction = $validated['direction'] ?? null;

        if ($direction === null) {
            return back()->with('erro', 'Informe a direção da movimentação.');
        }

        $siblingQuery = ProductionStep::where('loja_id', $lojaId)
            ->where('production_phase_id', $step->production_phase_id)
            ->where('ativo', true);

        $neighbor = $direction === 'up'
            ? (clone $siblingQuery)->where('ordem', '<', $step->ordem)->orderByDesc('ordem')->first()
            : (clone $siblingQuery)->where('ordem', '>', $step->ordem)->orderBy('ordem')->first();

        if (!$neighbor) {
            return back()->with('erro', 'A etapa já está no limite da fase.');
        }

        DB::transaction(function () use ($step, $neighbor): void {
            $currentOrder = (int) $step->ordem;
            $neighborOrder = (int) $neighbor->ordem;

            $step->update(['ordem' => 0]);
            $neighbor->update(['ordem' => $currentOrder]);
            $step->update(['ordem' => $neighborOrder]);
        });

        return back()->with('sucesso', 'Ordem da etapa atualizada.');
    }

    public function storeStepInPhase(Request $request, ProductionPhase $phase): RedirectResponse
    {
        $lojaId = (int) auth()->user()->loja_id;
        $this->ensureTenantPhase($phase, $lojaId);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $nextOrder = (int) ProductionStep::withTrashed()
            ->where('loja_id', $lojaId)
            ->where('production_phase_id', (int) $phase->id)
            ->max('ordem') + 1;

        ProductionStep::create([
            'loja_id' => $lojaId,
            'production_phase_id' => (int) $phase->id,
            'nome' => $validated['nome'],
            'ordem' => $nextOrder,
        ]);

        return back()->with('sucesso', 'Etapa adicionada na fase ' . $phase->nome . '.');
    }

    private function ensureTenantStep(ProductionStep $step, int $lojaId): void
    {
        abort_if((int) $step->loja_id !== $lojaId, 403, 'Etapa não pertence à loja ativa.');
    }

    private function ensureTenantPhase(ProductionPhase $phase, int $lojaId): void
    {
        abort_if((int) $phase->loja_id !== $lojaId, 403, 'Fase não pertence à loja ativa.');
    }

    private function wouldLeavePhaseWithoutSteps(int $lojaId, int $phaseId, int $stepId): bool
    {
        $remaining = ProductionStep::where('loja_id', $lojaId)
            ->where('production_phase_id', $phaseId)
            ->where('ativo', true)
            ->where('id', '!=', $stepId)
            ->count();

        return $remaining === 0;
    }

    private function normalizePhaseOrder(int $lojaId, int $phaseId): void
    {
        $steps = ProductionStep::withTrashed()
            ->where('loja_id', $lojaId)
            ->where('production_phase_id', $phaseId)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get(['id', 'ordem']);

        foreach ($steps as $index => $phaseStep) {
            $newOrder = $index + 1;

            if ((int) $phaseStep->ordem !== $newOrder) {
                ProductionStep::withTrashed()->whereKey($phaseStep->id)->update(['ordem' => $newOrder]);
            }
        }
    }
}
