<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Insumo;

/**
 * InventoryConversionService (LEGADO - Manter para compatibilidade)
 *
 * Este serviço é mantido por compatibilidade com código existente.
 * Novos desenvolvimentos devem usar InsumoConversaoService diretamente.
 *
 * Delegação para o novo serviço: InsumoConversaoService
 */
class InventoryConversionService
{
    public function __construct(
        private readonly InsumoConversaoService $insumoConversao,
    ) {}

    public function toBaseUnit(float $quantity, float $factor): float
    {
        return $this->insumoConversao->convertFromPurchaseToBase($quantity, $factor);
    }

    public function fromPurchaseUnitToBase(Insumo $insumo, float $quantityInPurchaseUnit): float
    {
        if (!$this->insumoConversao->hasSimpleConversion($insumo)) {
            return $quantityInPurchaseUnit;
        }

        $factor = $this->insumoConversao->getTotalFactor($insumo);
        return $this->insumoConversao->convertFromPurchaseToBase($quantityInPurchaseUnit, $factor);
    }

    public function getPackagingSummary(Insumo $insumo): array
    {
        $summary = $this->insumoConversao->getConversionSummary($insumo);

        // Compatibilidade com formato antigo
        return [
            'has_conversion' => $summary['has_conversion'],
            'has_two_levels' => $summary['has_two_levels'],
            'unit_base' => $summary['unit_base'],
            'unit_purchase' => $summary['unit_purchase'],
            'unit_sub' => (string) ($insumo->unidade_subunidade ?? ''),
            'factor_total' => $summary['factor_total'],
            'quantity_per_purchase' => (float) ($insumo->quantidade_por_compra ?? 1),
            'quantity_sub_per_purchase' => (float) ($insumo->quantidade_subunidades_por_compra ?? 0),
            'quantity_base_per_sub' => (float) ($insumo->quantidade_consumo_por_subunidade ?? 0),
            'description' => $summary['description'] ?? 'Sem conversão',
        ];
    }

    /**
     * Valida e normaliza estrutura de conversão.
     * Delegado ao novo service, mas com try-catch para compatibilidade.
     */
    public function validateConversionStructure(array $data): array
    {
        try {
            return $this->insumoConversao->validateAndNormalizeConversion($data);
        } catch (\InvalidArgumentException $e) {
            // Se houver erro de validação, retornar dados normalizados (sem 2 níveis)
            $data['unidade_subunidade'] = null;
            $data['quantidade_subunidades_por_compra'] = null;
            $data['quantidade_consumo_por_subunidade'] = null;
            // Poderia logar o erro aqui para análise
            return $data;
        }
    }
}
