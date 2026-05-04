<?php

declare(strict_types=1);

namespace App\Repositories;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-17 00:26
*/

use App\Models\FinancialPayment;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTitle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FinancialTitleRepository
{
    public function sumPaymentsByTitleTypeInPeriod(int $lojaId, string $tipo, string $inicio, string $fim): float
    {
        return (float) FinancialPayment::query()
            ->where('loja_id', $lojaId)
            ->whereHas('titulo', function ($query) use ($lojaId, $tipo) {
                $query->where('loja_id', $lojaId)->where('tipo', $tipo);
            })
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->sum('valor');
    }

    public function sumOpenBalanceByType(int $lojaId, string $tipo): float
    {
        return (float) FinancialTitle::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', $tipo)
            ->where('status', '!=', 'pago')
            ->sum('saldo_restante');
    }

    public function sumOverdueBalanceByType(int $lojaId, string $tipo): float
    {
        return (float) FinancialTitle::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', $tipo)
            ->where('status', 'vencido')
            ->sum('saldo_restante');
    }

    public function sumAccountsRealBalance(int $lojaId): float
    {
        $contas = FinancialAccount::query()
            ->where('loja_id', $lojaId)
            ->where('ativo', true)
            ->get();

        $saldoTotal = 0.0;

        foreach ($contas as $conta) {
            $saldo = (float) $conta->saldo_inicial;

            $entradas = FinancialPayment::query()
                ->where('loja_id', $lojaId)
                ->where('financial_account_id', $conta->id)
                ->whereHas('titulo', function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId)->where('tipo', 'receber');
                })
                ->sum('valor');

            $saidas = FinancialPayment::query()
                ->where('loja_id', $lojaId)
                ->where('financial_account_id', $conta->id)
                ->whereHas('titulo', function ($query) use ($lojaId) {
                    $query->where('loja_id', $lojaId)->where('tipo', 'pagar');
                })
                ->sum('valor');

            $saldo += (float) $entradas - (float) $saidas;
            $saldoTotal += $saldo;
        }

        return $saldoTotal;
    }

    public function getLatestTitlesWithCategoryByLoja(int $lojaId, int $limit = 10): Collection
    {
        return FinancialTitle::query()
            ->where('loja_id', $lojaId)
            ->with('categoria')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function paginateReceivablesByLoja(int $lojaId, ?string $status, int $perPage = 30): LengthAwarePaginator
    {
        $query = FinancialTitle::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', 'receber')
            ->with(['categoria', 'pedido']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->latest('data_vencimento')->paginate($perPage);
    }

    public function getReceitaCategoriesByLoja(int $lojaId): Collection
    {
        return FinancialCategory::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', 'receita')
            ->get();
    }

    public function paginatePayablesByLoja(int $lojaId, ?string $status, int $perPage = 30): LengthAwarePaginator
    {
        $query = FinancialTitle::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', 'pagar')
            ->with(['categoria']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->latest('data_vencimento')->paginate($perPage);
    }

    public function getDespesaCategoriesByLoja(int $lojaId): Collection
    {
        return FinancialCategory::query()
            ->where('loja_id', $lojaId)
            ->where('tipo', 'despesa')
            ->get();
    }

    public function getActiveAccountsByLoja(int $lojaId): Collection
    {
        return FinancialAccount::query()
            ->where('loja_id', $lojaId)
            ->where('ativo', true)
            ->get();
    }
}
