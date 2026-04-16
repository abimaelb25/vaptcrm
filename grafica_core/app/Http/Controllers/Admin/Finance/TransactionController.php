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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $inicio = $request->inicio ?? now()->startOfMonth()->toDateString();
        $fim = $request->fim ?? now()->toDateString();

        $query = MovimentacaoFinanceira::query()->with(['pedido', 'usuario']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $query->whereBetween('data_movimentacao', [$inicio, $fim]);

        $baseQuery = MovimentacaoFinanceira::where('status', 'pago');

        $entradasMes = (clone $baseQuery)->where('tipo', 'entrada')
            ->whereBetween('data_movimentacao', [$inicio, $fim])->sum('valor');

        $saidasMes = (clone $baseQuery)->where('tipo', 'saida')
            ->whereBetween('data_movimentacao', [$inicio, $fim])->sum('valor');

        $saldoAtual = MovimentacaoFinanceira::where('status', 'pago')
            ->sum(DB::raw('CASE WHEN tipo = "entrada" THEN valor ELSE -valor END'));

        $contasPendentes = MovimentacaoFinanceira::where('status', 'pendente')
            ->sum(DB::raw('CASE WHEN tipo = "entrada" THEN valor ELSE -valor END'));

        return view('painel.financeiro.index', [
            'movimentacoes' => $query->latest()->paginate(50),
            'recentes' => MovimentacaoFinanceira::with(['pedido'])->latest()->take(20)->get(),
            'saldoAtual' => $saldoAtual,
            'entradasMes' => $entradasMes,
            'saidasMes' => $saidasMes,
            'contasPendentes' => $contasPendentes,
            'inicio' => $inicio,
            'fim' => $fim,
        ]);
    }

    public function extrato(Request $request): View
    {
        $query = MovimentacaoFinanceira::query()->with(['pedido', 'usuario']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('inicio')) {
            $query->where('data_movimentacao', '>=', $request->inicio);
        }

        if ($request->filled('fim')) {
            $query->where('data_movimentacao', '<=', $request->fim);
        }

        return view('painel.financeiro.extrato', [
            'movimentacoes' => $query->latest()->paginate(50),
        ]);
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
