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
use App\Models\FinancialCategory;
use App\Models\FinancialAccount;
use App\Models\FinancialPayment;
use App\Services\Domain\FinanceService;
use App\Services\SaaS\FinancePlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialTitleController extends Controller
{
    public function __construct(
        protected FinanceService $financeService,
        protected FinancePlanService $planService
    ) {}

    /**
     * Dashboard Financeiro Profissional.
     */
    public function dashboard(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        $inicio = $request->inicio ?? now()->startOfMonth()->toDateString();
        $fim = $request->fim ?? now()->endOfMonth()->toDateString();

        // KPIs de Fluxo de Caixa (Realizado)
        $entradas = FinancialPayment::whereHas('titulo', fn($q) => $q->where('tipo', 'receber'))
            ->whereBetween('data_pagamento', [$inicio, $fim])->sum('valor');
            
        $saidas = FinancialPayment::whereHas('titulo', fn($q) => $q->where('tipo', 'pagar'))
            ->whereBetween('data_pagamento', [$inicio, $fim])->sum('valor');

        // KPIs de Previsão (Títulos em aberto no período)
        $receberTotal = FinancialTitle::where('tipo', 'receber')->where('status', '!=', 'pago')->sum('saldo_restante');
        $receberVencido = FinancialTitle::where('tipo', 'receber')->where('status', 'vencido')->sum('saldo_restante');
        
        $pagarTotal = FinancialTitle::where('tipo', 'pagar')->where('status', '!=', 'pago')->sum('saldo_restante');
        $pagarVencido = FinancialTitle::where('tipo', 'pagar')->where('status', 'vencido')->sum('saldo_restante');

        // Calcula saldo real das contas: saldo_inicial + entradas - saídas
        $saldoContas = $this->calcularSaldoContas();

        return view('painel.financeiro.dashboard', [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'receberTotal' => $receberTotal,
            'receberVencido' => $receberVencido,
            'pagarTotal' => $pagarTotal,
            'pagarVencido' => $pagarVencido,
            'saldoContas' => $saldoContas,
            'inicio' => $inicio,
            'fim' => $fim,
            'ultimosTitulos' => FinancialTitle::with('categoria')->latest()->take(10)->get(),
        ]);
    }

    /**
     * Listagem de Contas a Receber.
     */
    public function receivable(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        if (!$this->planService->canUsePro()) {
            return view('painel.billing.upgrade_needed', ['feature' => 'Gestão de Contas a Receber']);
        }

        $query = FinancialTitle::where('tipo', 'receber')->with(['categoria', 'pedido']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('painel.financeiro.titulos.index', [
            'tipo' => 'receber',
            'titulos' => $query->latest('data_vencimento')->paginate(30),
            'categorias' => FinancialCategory::where('tipo', 'receita')->get(),
            'contas' => FinancialAccount::where('ativo', true)->get(),
        ]);
    }

    /**
     * Listagem de Contas a Pagar.
     */
    public function payable(Request $request): View
    {
        $this->authorize('viewAny', FinancialTitle::class);

        if (!$this->planService->canUsePro()) {
            return view('painel.billing.upgrade_needed', ['feature' => 'Gestão de Contas a Pagar']);
        }

        $query = FinancialTitle::where('tipo', 'pagar')->with(['categoria']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('painel.financeiro.titulos.index', [
            'tipo' => 'pagar',
            'titulos' => $query->latest('data_vencimento')->paginate(30),
            'categorias' => FinancialCategory::where('tipo', 'despesa')->get(),
            'contas' => FinancialAccount::where('ativo', true)->get(),
        ]);
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

        $data['loja_id'] = auth()->user()->loja_id;
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

        $data = $request->validate([
            'valor' => ['required', 'numeric', 'min:0.01', 'max:' . $title->saldo_restante],
            'forma_pagamento' => ['required', 'string'],
            'data_pagamento' => ['required', 'date'],
            'account_id' => ['nullable', 'exists:financial_accounts,id'],
        ]);

        $this->financeService->addPayment($title, $data);

        return back()->with('sucesso', 'Pagamento registrado com sucesso.');
    }

    /**
     * Calcula o saldo real das contas financeiras.
     * Saldo = saldo_inicial + entradas realizadas - saídas realizadas
     */
    protected function calcularSaldoContas(): float
    {
        $contas = FinancialAccount::where('ativo', true)->get();
        $saldoTotal = 0;

        foreach ($contas as $conta) {
            // Saldo inicial da conta
            $saldo = (float) $conta->saldo_inicial;

            // Soma pagamentos recebidos (receber) nesta conta
            $entradas = FinancialPayment::where('financial_account_id', $conta->id)
                ->whereHas('titulo', fn($q) => $q->where('tipo', 'receber'))
                ->sum('valor');

            // Soma pagamentos feitos (pagar) desta conta
            $saidas = FinancialPayment::where('financial_account_id', $conta->id)
                ->whereHas('titulo', fn($q) => $q->where('tipo', 'pagar'))
                ->sum('valor');

            $saldo += (float) $entradas - (float) $saidas;
            $saldoTotal += $saldo;
        }

        return $saldoTotal;
    }
}
