<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Insumo;

/**
 * InsumoConversaoService
 *
 * Responsável por:
 * - Converter entre unidade de compra e unidade base
 * - Validar estrutura de conversão sem ambiguidades
 * - Gerar previsualizações claras
 * - Impedir composições semânticas inválidas
 *
 * Regras inegociáveis:
 * 1. Sem conversão: compra e base são mesma unidade
 * 2. Um nível: 1 compra = N base (ex: 1 frasco = 1000 ml)
 * 3. Dois níveis: 1 compra = M sub, cada sub = N base (ex: 1 caixa = 4 frascos, cada frasco = 1000 ml)
 *
 * PROIBIDO: unidade_compra == unidade_subunidade (confusão semântica)
 * PROIBIDO: quantidade_por_compra > 1 quando há 2 níveis (redundância, use quantidade_subunidades)
 */
final class InsumoConversaoService
{
    private function normalizarTokenUnidade(string $value): string
    {
        $token = trim(mb_strtolower($value));

        $token = strtr($token, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ]);

        $token = preg_replace('/\s+/', '', $token) ?? $token;

        if (strlen($token) > 3 && str_ends_with($token, 's')) {
            $token = substr($token, 0, -1);
        }

        return $token;
    }

    private function unidadesSaoEquivalentes(string $first, string $second): bool
    {
        return $this->normalizarTokenUnidade($first) === $this->normalizarTokenUnidade($second);
    }

    public function hasPurchaseConversion(Insumo $insumo): bool
    {
        return $this->hasTwoLevelConversion($insumo)
            || (!empty($insumo->unidade_compra) && ((float) ($insumo->quantidade_por_compra ?? 0)) > 1.0);
    }

    /**
     * Converte quantidade de unidade de compra para unidade base.
     *
     * @param float $quantityInPurchaseUnit Quantidade na unidade de compra
     * @param float $factor Fator total de conversão
     * @return float Quantidade convertida para unidade base
     */
    public function convertFromPurchaseToBase(float $quantityInPurchaseUnit, float $factor): float
    {
        return $quantityInPurchaseUnit * max(0.0001, $factor);
    }

    /**
     * Converte quantidade de unidade base para unidade de compra.
     *
     * @param float $quantityInBase Quantidade em unidade base
     * @param float $factor Fator total de conversão
     * @return float Quantidade convertida para unidade de compra
     */
    public function convertFromBaseToPurchase(float $quantityInBase, float $factor): float
    {
        return $quantityInBase / max(0.0001, $factor);
    }

    /**
     * Retorna se o insumo possui conversão simples (1 nível).
     *
     * Exemplo: 1 pacote = 500 folhas
     */
    public function hasSimpleConversion(Insumo $insumo): bool
    {
        return !empty($insumo->unidade_compra)
            && !$this->hasTwoLevelConversion($insumo)
            && ((float) $insumo->quantidade_por_compra) > 1.0;
    }

    /**
     * Retorna se o insumo possui conversão em dois níveis.
     *
     * Exemplo: 1 caixa = 6 frascos, cada frasco = 100 ml
     */
    public function hasTwoLevelConversion(Insumo $insumo): bool
    {
        return !empty($insumo->unidade_compra)
            && !empty($insumo->unidade_subunidade)
            && ((float) ($insumo->quantidade_subunidades_por_compra ?? 0)) > 0.0
            && ((float) ($insumo->quantidade_consumo_por_subunidade ?? 0)) > 0.0;
    }

    /**
     * Retorna o fator total de conversão.
     *
     * Sem conversão: fator = 1
     * Um nível: fator = quantidade_por_compra
     * Dois níveis: fator = quantidade_subunidades_por_compra × quantidade_consumo_por_subunidade
     */
    public function getTotalFactor(Insumo $insumo): float
    {
        if ($this->hasTwoLevelConversion($insumo)) {
            $sub = max(0.0001, (float) $insumo->quantidade_subunidades_por_compra);
            $perSub = max(0.0001, (float) $insumo->quantidade_consumo_por_subunidade);
            return $sub * $perSub;
        }

        if ($this->hasSimpleConversion($insumo)) {
            return max(0.0001, (float) $insumo->quantidade_por_compra);
        }

        return 1.0;
    }

    /**
     * Gera resumo de conversão em linguagem operacional clara.
     *
     * Exemplos de saída:
     * - "Sem conversão"
     * - "1 pacote contém 500 folhas"
     * - "1 caixa contém 6 frascos de 100 ml cada (total: 600 ml)"
     */
    public function getConversionSummary(Insumo $insumo): array
    {
        $summary = [
            'has_conversion' => false,
            'has_two_levels' => false,
            'unit_base' => (string) $insumo->unidade_medida,
            'unit_purchase' => '',
            'factor_total' => 1.0,
        ];

        if (!$this->hasPurchaseConversion($insumo)) {
            return $summary;
        }

        $summary['has_conversion'] = true;
        $summary['unit_purchase'] = (string) $insumo->unidade_compra;

        if ($this->hasTwoLevelConversion($insumo)) {
            $summary['has_two_levels'] = true;
            $qtdSub = (float) $insumo->quantidade_subunidades_por_compra;
            $qtdPer = (float) $insumo->quantidade_consumo_por_subunidade;
            $factor = $qtdSub * $qtdPer;

            $summary['factor_total'] = $factor;
            $summary['lines'] = [
                sprintf('Unidade de estoque: %s', $insumo->unidade_medida),
                sprintf('Compra em: %s', $insumo->unidade_compra),
                sprintf(
                    'Cada %s possui: %s %s',
                    $insumo->unidade_compra,
                    rtrim(rtrim(number_format($qtdSub, 4, '.', ''), '0'), '.'),
                    $insumo->unidade_subunidade,
                ),
                sprintf(
                    'Cada %s possui: %s %s',
                    $insumo->unidade_subunidade,
                    rtrim(rtrim(number_format($qtdPer, 4, '.', ''), '0'), '.'),
                    $insumo->unidade_medida,
                ),
                sprintf(
                    'Total por %s: %s %s',
                    $insumo->unidade_compra,
                    rtrim(rtrim(number_format($factor, 4, '.', ''), '0'), '.'),
                    $insumo->unidade_medida,
                ),
            ];
            $summary['description'] = implode(' | ', $summary['lines']);
        } else {
            $factor = (float) $insumo->quantidade_por_compra;
            $summary['factor_total'] = $factor;
            $summary['lines'] = [
                sprintf('Unidade de estoque: %s', $insumo->unidade_medida),
                sprintf('Compra em: %s', $insumo->unidade_compra),
                sprintf(
                    'Cada %s equivale a: %s %s',
                    $insumo->unidade_compra,
                    rtrim(rtrim(number_format($factor, 4, '.', ''), '0'), '.'),
                    $insumo->unidade_medida,
                ),
            ];
            $summary['description'] = implode(' | ', $summary['lines']);
        }

        return $summary;
    }

    /**
     * Valida e normaliza estrutura de conversão, removendo ambiguidades.
     *
     * Regras:
     * 1. Se unidade_compra vazia: limpar tudo, sem conversão
     * 2. Se quantidade_por_compra ≤ 1: sem conversão simples
     * 3. Se há 2 níveis: validar que unidade_subunidade ≠ unidade_compra
     * 4. Se há 2 níveis: quantidade_por_compra deve ser 1 ou não existir
     *
     * @param array $data Dados do insumo
     * @return array Dados normalizados
     * @throws \InvalidArgumentException Se estrutura for semanticamente inválida
     */
    public function validateAndNormalizeConversion(array $data): array
    {
        $normalized = $data;

        // Sem unidade de compra = sem conversão
        if (empty($normalized['unidade_compra'])) {
            $normalized['unidade_compra'] = null;
            $normalized['quantidade_por_compra'] = 1;
            $normalized['unidade_subunidade'] = null;
            $normalized['quantidade_subunidades_por_compra'] = null;
            $normalized['quantidade_consumo_por_subunidade'] = null;
            return $normalized;
        }

        $normalized['unidade_compra'] = trim((string) $normalized['unidade_compra']);
        $normalized['unidade_subunidade'] = isset($normalized['unidade_subunidade'])
            ? trim((string) $normalized['unidade_subunidade'])
            : null;

        // Normaliza valores nulos
        $qtyPerCompra = (float) ($normalized['quantidade_por_compra'] ?? 1);
        $hasTwoLevelRequest = !empty($normalized['unidade_subunidade'])
            && ((float) ($normalized['quantidade_subunidades_por_compra'] ?? 0)) > 0.0
            && ((float) ($normalized['quantidade_consumo_por_subunidade'] ?? 0)) > 0.0;

        if (!$hasTwoLevelRequest && $qtyPerCompra <= 1.0) {
            $normalized['quantidade_por_compra'] = 1;
            $normalized['unidade_subunidade'] = null;
            $normalized['quantidade_subunidades_por_compra'] = null;
            $normalized['quantidade_consumo_por_subunidade'] = null;
            return $normalized;
        }

        if (!$hasTwoLevelRequest) {
            // Conversão simples (1 nível)
            if ($qtyPerCompra <= 1.0) {
                throw new \InvalidArgumentException(
                    "Informe quantos {$normalized['unidade_medida']} existem em cada {$normalized['unidade_compra']}."
                );
            }

            $normalized['unidade_subunidade'] = null;
            $normalized['quantidade_subunidades_por_compra'] = null;
            $normalized['quantidade_consumo_por_subunidade'] = null;
            $normalized['quantidade_por_compra'] = $qtyPerCompra;
            return $normalized;
        }

        // Validação de 2 níveis
        $unitPurchase = (string) $normalized['unidade_compra'];
        $unitSub = (string) $normalized['unidade_subunidade'];

        // ERRO CRÍTICO: unidades iguais causam confusão semântica
        if ($this->unidadesSaoEquivalentes($unitPurchase, $unitSub)) {
            throw new \InvalidArgumentException(
                "A unidade de compra ({$unitPurchase}) não pode ser igual à unidade intermediária ({$unitSub}). "
                . "Use nomes diferentes para evitar ambiguidade. "
                . "Exemplo correto: Compra em 'caixa', intermediária em 'frasco'."
            );
        }

        // Para 2 níveis, quantidade_por_compra deve ser 1 ou coincidir com subunidades
        // Na verdade, quantidade_por_compra não é usado em 2 níveis, apenas quantidade_subunidades_por_compra
        $normalized['quantidade_por_compra'] = 1;

        return $normalized;
    }

    /**
     * Gera preview de custo com conversão clara para UI.
     *
     * @param Insumo $insumo
     * @return array
     */
    public function generateCostPreview(Insumo $insumo): array
    {
        $custoEfetivo = $insumo->getCustoEfetivo();
        $factor = $this->getTotalFactor($insumo);

        $preview = [
            'custo_por_unidade_base' => $custoEfetivo,
            'unidade_base' => $insumo->unidade_medida,
            'custo_por_unidade_consumo' => $custoEfetivo,
            'unidade_consumo' => $insumo->unidade_medida,
        ];

        if ($this->hasPurchaseConversion($insumo)) {
            $preview['custo_por_unidade_compra'] = $custoEfetivo * $factor;
            $preview['unidade_compra'] = $insumo->unidade_compra;
        }

        if ($this->hasTwoLevelConversion($insumo)) {
            $qtdPer = (float) $insumo->quantidade_consumo_por_subunidade;
            $preview['custo_por_subunidade'] = $custoEfetivo * $qtdPer;
            $preview['unidade_subunidade'] = $insumo->unidade_subunidade;
        }

        return $preview;
    }
}
