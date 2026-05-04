<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Services\Domain\ProductionService;
use App\Services\SaaS\FeatureGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionKanbanController extends Controller
{
    public function __construct(
        protected ProductionService $productionService,
        protected FeatureGateService $featureGateService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $lojaId = (int) $request->user()->loja_id;

        if (! $this->featureGateService->allows('modulo_kanban', $lojaId)) {
            return redirect()->route('admin.billing.index')->with('warning', 'Kanban de producao indisponivel no plano atual. Faça upgrade para liberar.');
        }

        return view('painel.operacoes.producao.kanban', [
            'kanban' => $this->productionService->getKanban($lojaId),
            'metrics' => $this->productionService->getProductionMetrics($lojaId),
        ]);
    }
}
