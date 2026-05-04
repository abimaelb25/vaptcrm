<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\EstoqueMovimentacao;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;

class InventoryMovementService
{
    public function __construct(
        private readonly InventoryConversionService $conversionService,
        private readonly InventoryCostService $costService,
        private readonly InventoryAuditService $auditService,
    ) {}

    public function registerEntry(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $insumo = Insumo::query()->lockForUpdate()->findOrFail($insumo->id);

            $quantityInput = (float) $data['quantidade'];
            $costInput = (float) ($data['custo_unitario'] ?? 0);
            $inPurchaseUnit = (bool) ($data['em_unidade_compra'] ?? false);

            $factor = $insumo->getFatorTotalConversao();
            $quantityBase = $inPurchaseUnit && $insumo->temConversaoUnidade()
                ? $this->conversionService->fromPurchaseUnitToBase($insumo, $quantityInput)
                : $quantityInput;

            $costPerBase = $inPurchaseUnit && $insumo->temConversaoUnidade()
                ? $this->costService->calculateConsumptionCostFromPurchase($costInput, $factor)
                : $costInput;

            $valueTotal = $quantityBase * $costPerBase;
            $stockBefore = (float) $insumo->estoque_atual;
            $stockAfter = $stockBefore;

            if ((bool) $insumo->controlar_estoque) {
                $stockAfter = $stockBefore + $quantityBase;
                $avgCost = $this->costService->calculateUpdatedAverageCost(
                    $stockBefore,
                    (float) $insumo->custo_medio,
                    $quantityBase,
                    $costPerBase
                );

                $insumo->update([
                    'estoque_atual' => $stockAfter,
                    'custo_medio' => $avgCost,
                    'ultimo_custo' => $costPerBase,
                    'custo_unitario_consumo' => $avgCost,
                ]);
            } else {
                $insumo->update([
                    'ultimo_custo' => $costPerBase,
                    'custo_medio' => $costPerBase,
                    'custo_unitario_consumo' => $costPerBase,
                ]);
            }

            $movement = EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'entrada',
                'origem' => $data['origem'] ?? 'compra',
                'quantidade' => (bool) $insumo->controlar_estoque ? $quantityBase : 0,
                'quantidade_base' => (bool) $insumo->controlar_estoque ? $quantityBase : 0,
                'custo_unitario' => $costPerBase,
                'valor_total' => $valueTotal,
                'fornecedor_id' => $data['fornecedor_id'] ?? null,
                'data_movimentacao' => $data['data_movimentacao'] ?? now(),
                'usuario_id' => auth()->id(),
                'descricao' => (bool) $insumo->controlar_estoque
                    ? ($data['descricao'] ?? null)
                    : trim((string) (($data['descricao'] ?? '') . ' [item sem controle de estoque]')),
                'saldo_anterior' => $stockBefore,
                'saldo_posterior' => $stockAfter,
                'origem_tela' => $data['origem_tela'] ?? 'entrada',
                'motivo' => null,
                'metadata' => [
                    'entrada_em_unidade_compra' => $inPurchaseUnit,
                    'fator_conversao' => $factor,
                    'quantidade_informada' => $quantityInput,
                    'custo_informado' => $costInput,
                    'controlar_estoque' => (bool) $insumo->controlar_estoque,
                ],
            ]);

            $this->auditService->logMovement(
                $movement,
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockBefore,
                ],
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockAfter,
                    'quantidade_base' => $movement->quantidade_base,
                    'custo_unitario' => $movement->custo_unitario,
                ]
            );

            return $movement;
        });
    }

    public function registerOutput(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $insumo = Insumo::query()->lockForUpdate()->findOrFail($insumo->id);

            $quantityBase = (float) $data['quantidade'];
            $stockBefore = (float) $insumo->estoque_atual;
            $stockAfter = $stockBefore - $quantityBase;

            $insumo->decrement('estoque_atual', $quantityBase);

            $movement = EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'saida',
                'origem' => $data['origem'] ?? 'manual',
                'quantidade' => $quantityBase,
                'quantidade_base' => $quantityBase,
                'data_movimentacao' => $data['data_movimentacao'] ?? now(),
                'usuario_id' => auth()->id(),
                'descricao' => $data['descricao'] ?? null,
                'saldo_anterior' => $stockBefore,
                'saldo_posterior' => $stockAfter,
                'origem_tela' => $data['origem_tela'] ?? 'saida',
                'motivo' => $data['origem'] ?? 'manual',
                'metadata' => [
                    'tipo_saida' => $data['origem'] ?? 'manual',
                ],
            ]);

            $this->auditService->logMovement(
                $movement,
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockBefore,
                ],
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockAfter,
                    'quantidade_base' => $movement->quantidade_base,
                ]
            );

            return $movement;
        });
    }

    public function registerAdjustment(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return DB::transaction(function () use ($insumo, $data) {
            $insumo = Insumo::query()->lockForUpdate()->findOrFail($insumo->id);

            $stockBefore = (float) $insumo->estoque_atual;
            $stockAfter = (float) $data['quantidade'];
            $quantityBase = $stockAfter - $stockBefore;

            $reason = (string) ($data['motivo_rapido'] ?? 'ajuste_manual');
            $reasonDetail = trim((string) ($data['detalhe_motivo'] ?? ''));

            $reasonMap = [
                'balanco_mensal' => 'Balanco mensal',
                'correcao_lancamento' => 'Correcao de lancamento',
                'perda_vazamento' => 'Perda / vazamento',
                'inventario_fisico' => 'Inventario fisico',
                'ajuste_manual' => 'Ajuste manual',
                'outro' => 'Outro',
            ];

            $reasonLabel = $reasonMap[$reason] ?? 'Ajuste manual';
            $description = 'Ajuste de saldo - Motivo: ' . $reasonLabel;
            if ($reasonDetail !== '') {
                $description .= ' | Detalhe: ' . $reasonDetail;
            }

            // Ajuste inventarial altera saldo, mas nao recalcula custo medio como compra.
            $insumo->update(['estoque_atual' => $stockAfter]);

            $movement = EstoqueMovimentacao::create([
                'loja_id' => $insumo->loja_id,
                'insumo_id' => $insumo->id,
                'tipo' => 'ajuste',
                'origem' => 'ajuste',
                'quantidade' => $quantityBase,
                'quantidade_base' => $quantityBase,
                'data_movimentacao' => now(),
                'usuario_id' => auth()->id(),
                'descricao' => $description,
                'saldo_anterior' => $stockBefore,
                'saldo_posterior' => $stockAfter,
                'origem_tela' => $data['origem_tela'] ?? 'ajuste',
                'motivo' => $reason,
                'metadata' => [
                    'motivo_rapido' => $reason,
                    'detalhe_motivo' => $reasonDetail,
                    'tipo_ajuste' => $quantityBase >= 0 ? 'positivo' : 'negativo',
                ],
            ]);

            $this->auditService->logAdjustment(
                $movement,
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockBefore,
                ],
                [
                    'insumo_id' => $insumo->id,
                    'estoque_atual' => $stockAfter,
                    'diferenca_lancada' => $quantityBase,
                    'motivo_rapido' => $reason,
                    'detalhe_motivo' => $reasonDetail,
                ]
            );

            return $movement;
        });
    }
}
