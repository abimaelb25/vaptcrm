<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Finance;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:40
| Descrição: Controller para gestão de Títulos Financeiros (A Pagar / A Receber).
*/

use App\Http\Controllers\Controller;
use App\Models\FinancialTitle;
use App\Services\Domain\FinanceApplicationService;
use App\Services\Domain\FinanceService;
use App\Services\SaaS\FinancePlanService;
use App\Services\SaaS\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialTitleController extends Controller
{
    public function __construct(
        protected FinanceApplicationService $financeApplicationService,
        protected FinanceService $financeService,
        protected FinancePlanService $planService,
        protected TenantContext $tenantContext,
    ) {}

    /**
     * Dashboard Financeiro Profissional.
     */
    public function dashboard(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        $inicio = $request->inicio ?? now()->startOfMonth()->toDateString();
        $fim = $request->fim ?? now()->endOfMonth()->toDateString();

        return view('painel.financeiro.dashboard', $this->financeApplicationService->dashboardPayload($inicio, $fim));
    }

    /**
     * Listagem de Contas a Receber.
     */
    public function receivable(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        $result = $this->financeApplicationService->receivablePayload(
            $request->filled('status') ? (string) $request->status : null
        );

        return view($result['view'], $result['data']);
    }

    /**
     * Listagem de Contas a Pagar.
     */
    public function payable(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        $result = $this->financeApplicationService->payablePayload(
            $request->filled('status') ? (string) $request->status : null
        );

        return view($result['view'], $result['data']);
    }

    /**
     * Criar título manual (Despesa ou Receita Avulsa).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FinancialTitle::class);

        $data = $request->validate([
            'tipo' => ['required', 'in:receber,pagar'],
            'descricao' => ['required', 'string', 'max:255'],
            'valor_total' => ['required', 'numeric', 'min:0'],
            'data_vencimento' => ['required', 'date'],
            'categoria_id' => ['nullable', 'exists:financial_categories,id'],
            'observacao' => ['nullable', 'string'],
        ]);

        $data['loja_id'] = $this->tenantContext->getLojaId() ?? auth()->user()->loja_id;
        $data['data_emissao'] = now();
        $data['saldo_restante'] = $data['valor_total'];
        $data['status'] = now()->isAfter($data['data_vencimento']) ? 'vencido' : 'aberto';

        FinancialTitle::create($data);

        return back()->with('sucesso', 'Título financeiro criado.');
    }

    /**
     * Registrar pagamento em um título.
     */
    public function pay(Request $request, FinancialTitle $title): RedirectResponse
    {
        $this->authorize('update', $title);

        $lojaId = $this->tenantContext->getLojaId() ?? auth()->user()->loja_id;

        $data = $request->validate([
            'valor' => ['required', 'numeric', 'min:0.01', 'max:' . $title->saldo_restante],
            'forma_pagamento' => ['required', 'string'],
            'data_pagamento' => ['required', 'date'],
            'account_id' => [
                'nullable', 
                'integer',
                function ($attribute, $value, $fail) use ($lojaId) {
                    if ($value && !\App\Models\FinancialAccount::where('id', $value)->where('loja_id', $lojaId)->exists()) {
                        $fail('Conta bancária inválida ou não pertence a esta loja.');
                    }
                },
            ],
        ]);

        $this->financeService->addPayment($title, $data);

        return back()->with('sucesso', 'Pagamento registrado com sucesso.');
    }

}
