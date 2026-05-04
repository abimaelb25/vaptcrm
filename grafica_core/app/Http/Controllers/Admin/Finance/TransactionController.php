<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Finance;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use App\Http\Controllers\Controller;
use App\Models\MovimentacaoFinanceira;
use App\Services\Domain\FinanceApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected FinanceApplicationService $financeApplicationService,
    ) {}

    public function index(Request $request): View
    {
        $inicio = $request->inicio ?? now()->startOfMonth()->toDateString();
        $fim = $request->fim ?? now()->toDateString();

        return view('painel.financeiro.index', $this->financeApplicationService->transactionIndexPayload(
            $inicio,
            $fim,
            $request->filled('tipo') ? (string) $request->tipo : null
        ));
    }

    public function extrato(Request $request): View
    {
        return view('painel.financeiro.extrato', $this->financeApplicationService->transactionExtractPayload(
            $request->filled('tipo') ? (string) $request->tipo : null,
            $request->filled('inicio') ? (string) $request->inicio : null,
            $request->filled('fim') ? (string) $request->fim : null
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'tipo'              => ['required', 'in:entrada,saida'],
            'categoria'         => ['required', 'string', 'max:100'],
            'valor'             => ['required', 'numeric', 'min:0'],
            'data_movimentacao' => ['required', 'date'],
            'forma_pagamento'   => ['required', 'string'],
            'descricao'         => ['nullable', 'string', 'max:255'],
        ]);

        MovimentacaoFinanceira::create(array_merge($dados, [
            'status'     => MovimentacaoFinanceira::STATUS_PAGO,
            'usuario_id' => auth()->id(),
        ]));

        return back()->with('sucesso', 'Lançamento financeiro realizado.');
    }

    public function destroy(MovimentacaoFinanceira $movimentacao): RedirectResponse
    {
        $movimentacao->delete();
        return back()->with('sucesso', 'Lançamento removido.');
    }
}
