<?php

declare(strict_types=1);

namespace App\Repositories;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-17 00:27
*/

use App\Models\MovimentacaoFinanceira;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FinancialTransactionRepository
{
    public function paginateForIndexByLoja(
        int $lojaId,
        string $inicio,
        string $fim,
        ?string $tipo,
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = MovimentacaoFinanceira::query()
            ->where('loja_id', $lojaId)
            ->with(['pedido', 'usuario'])
            ->whereBetween('data_movimentacao', [$inicio, $fim]);

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getRecentesByLoja(int $lojaId, int $limit = 20): Collection
    {
        return MovimentacaoFinanceira::query()
            ->where('loja_id', $lojaId)
            ->with(['pedido'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function sumByTipoStatusNoPeriodo(
        int $lojaId,
        string $tipo,
        string $status,
        string $inicio,
        string $fim
    ): float {
        return (float) MovimentacaoFinanceira::query()
            ->where('loja_id', $lojaId)
            ->where('status', $status)
            ->where('tipo', $tipo)
            ->whereBetween('data_movimentacao', [$inicio, $fim])
            ->sum('valor');
    }

    public function sumSaldoPorStatus(int $lojaId, string $status): float
    {
        return (float) MovimentacaoFinanceira::query()
            ->where('loja_id', $lojaId)
            ->where('status', $status)
            ->selectRaw('COALESCE(SUM(CASE WHEN tipo = ? THEN valor ELSE -valor END), 0) as saldo', [MovimentacaoFinanceira::TIPO_ENTRADA])
            ->value('saldo');
    }

    public function paginateExtratoByLoja(
        int $lojaId,
        ?string $tipo,
        ?string $inicio,
        ?string $fim,
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = MovimentacaoFinanceira::query()
            ->where('loja_id', $lojaId)
            ->with(['pedido', 'usuario']);

        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }

        if (!empty($inicio)) {
            $query->where('data_movimentacao', '>=', $inicio);
        }

        if (!empty($fim)) {
            $query->where('data_movimentacao', '<=', $fim);
        }

        return $query->latest()->paginate($perPage);
    }
}
