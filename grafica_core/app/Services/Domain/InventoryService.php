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

class InventoryService
{
    public function __construct(
        protected InventoryMovementService $movementService,
    ) {}

    /**
     * Registra entrada de insumo e recalcula o custo médio.
     *
     * Por padrão, $data['quantidade'] e $data['custo_unitario'] estão em UNIDADE DE CONSUMO.
     *
     * Para lançamentos na UNIDADE DE COMPRA (ex: entrada de 3 pacotes de 500 folhas):
     *   - Passe $data['em_unidade_compra'] = true
     *   - $data['quantidade'] = quantidade de embalagens/compras recebidas (ex: 3)
     *   - $data['custo_unitario'] = custo por embalagem/compra (ex: 80.00)
     *   - O serviço converte internamente usando $insumo->quantidade_por_compra
     *
     * Casos sem conversão (unidade_compra = null ou quantidade_por_compra = 1):
     *   O flag em_unidade_compra é ignorado — sem impacto para dados legados.
     */
    public function registrarEntrada(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return $this->movementService->registerEntry($insumo, $data);
    }

    /**
     * Registra saída de insumo.
     */
    public function registrarSaida(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return $this->movementService->registerOutput($insumo, $data);
    }

    /**
     * Realiza ajuste de estoque forçando um novo saldo.
     */
    public function registrarAjuste(Insumo $insumo, array $data): EstoqueMovimentacao
    {
        return $this->movementService->registerAdjustment($insumo, $data);
    }
}
