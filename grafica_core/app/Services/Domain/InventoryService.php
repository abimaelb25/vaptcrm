<?php

declare(strict_types=1);

namespace App\Services\Domain;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 18:30
| Descrição: Serviço de gestão de estoque de insumos e custo médio.
*/

use App\Models\Insumo;
use App\Models\EstoqueMovimentacao;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Registra entrada de insumo e recalcula o custo médio.
     */
    public function registrarEntrada(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $qtd = (float) $data['quantidade'];
            $custoUnitario = (float) ($data['custo_unitario'] ?? 0);
            $valorTotal = $qtd * $custoUnitario;

            // 1. Recalcular Custo Médio
            $estoqueAnterior = (float) $insumo->estoque_atual;
            $custoMedioAnterior = (float) $insumo->custo_medio;
            
            $novoEstoqueTotal = $estoqueAnterior + $qtd;
            
            if ($novoEstoqueTotal > 0) {
                $novoCustoMedio = (($estoqueAnterior * $custoMedioAnterior) + $valorTotal) / $novoEstoqueTotal;
            } else {
                $novoCustoMedio = $custoUnitario;
            }

            // 2. Atualizar Insumo
            $insumo->update([
                'estoque_atual' => $novoEstoqueTotal,
                'custo_medio' => $novoCustoMedio,
                'ultimo_custo' => $custoUnitario,
            ]);

            // 3. Registrar Movimentação
            return EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'entrada',
                'origem' => $data['origem'] ?? 'compra',
                'quantidade' => $qtd,
                'custo_unitario' => $custoUnitario,
                'valor_total' => $valorTotal,
                'fornecedor_id' => $data['fornecedor_id'] ?? null,
                'data_movimentacao' => $data['data_movimentacao'] ?? now(),
                'usuario_id' => auth()->id(),
                'descricao' => $data['descricao'] ?? null,
            ]);
        });
    }

    /**
     * Registra saída de insumo.
     */
    public function registrarSaida(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $qtd = (float) $data['quantidade'];

            // 1. Atualizar Insumo
            $insumo->decrement('estoque_atual', $qtd);

            // 2. Registrar Movimentação
            return EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'saida',
                'origem' => $data['origem'] ?? 'manual',
                'quantidade' => $qtd,
                'data_movimentacao' => $data['data_movimentacao'] ?? now(),
                'usuario_id' => auth()->id(),
                'descricao' => $data['descricao'] ?? null,
            ]);
        });
    }

    /**
     * Realiza ajuste de estoque forçando um novo saldo.
     */
    public function registrarAjuste(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $novoSaldo = (float) $data['quantidade'];
            $qtdMovimentada = $novoSaldo - $insumo->estoque_atual;

            // 1. Atualizar Insumo
            $insumo->update(['estoque_atual' => $novoSaldo]);

            // 2. Registrar Movimentação
            return EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'ajuste',
                'origem' => 'ajuste',
                'quantidade' => $qtdMovimentada,
                'data_movimentacao' => now(),
                'usuario_id' => auth()->id(),
                'descricao' => $data['descricao'] ?? 'Ajuste anual/periódico de estoque',
            ]);
        });
    }
}
