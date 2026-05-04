<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Insumo;
use Tests\TestCase;

final class InsumoCostConversionTest extends TestCase
{
    public function test_simple_conversion_legacy_cost_is_normalized_from_purchase_to_consumption(): void
    {
        $insumo = new Insumo([
            'nome' => 'Papel offset A4',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'custo_medio' => 58.65,
            'custo_unitario_consumo' => null,
        ]);

        $this->assertEqualsWithDelta(1.46625, $insumo->getCustoEfetivo(), 0.000001);
        $this->assertEqualsWithDelta(58.65, $insumo->getCustoPorUnidadeCompraEfetivo(), 0.000001);
    }

    public function test_simple_conversion_prefers_consumption_cost_when_available(): void
    {
        $insumo = new Insumo([
            'nome' => 'Papel offset A4',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'custo_medio' => 58.65,
            'custo_unitario_consumo' => 1.46625,
        ]);

        $this->assertEqualsWithDelta(1.46625, $insumo->getCustoEfetivo(), 0.000001);
        $this->assertEqualsWithDelta(58.65, $insumo->getCustoPorUnidadeCompraEfetivo(), 0.000001);
    }

    public function test_resumo_conversao_custos_for_simple_conversion(): void
    {
        $insumo = new Insumo([
            'nome' => 'Papel offset A4',
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'custo_medio' => 58.65,
            'custo_unitario_consumo' => null,
        ]);

        $resumo = $insumo->getResumoConversaoCustos();

        $this->assertSame('folha', $resumo['unidade_consumo']);
        $this->assertSame('pacote', $resumo['unidade_compra']);
        $this->assertFalse($resumo['tem_dois_niveis']);
        $this->assertEqualsWithDelta(1.46625, $resumo['custo_por_unidade_consumo'], 0.000001);
        $this->assertEqualsWithDelta(58.65, $resumo['custo_por_unidade_compra'], 0.000001);
    }

    public function test_resumo_conversao_custos_for_two_levels(): void
    {
        $insumo = new Insumo([
            'nome' => 'Cola de silicone liquida 100ml',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 600,
            'quantidade_subunidades_por_compra' => 6,
            'unidade_subunidade' => 'frasco',
            'quantidade_consumo_por_subunidade' => 100,
            'custo_medio' => 0.0833,
            'custo_unitario_consumo' => 0.0833,
        ]);

        $resumo = $insumo->getResumoConversaoCustos();

        $this->assertSame('ml', $resumo['unidade_consumo']);
        $this->assertSame('caixa', $resumo['unidade_compra']);
        $this->assertSame('frasco', $resumo['unidade_subunidade']);
        $this->assertTrue($resumo['tem_dois_niveis']);
        $this->assertEqualsWithDelta(49.98, $resumo['custo_por_unidade_compra'], 0.000001);
        $this->assertEqualsWithDelta(8.33, $resumo['custo_por_subunidade'], 0.000001);
        $this->assertEqualsWithDelta(0.0833, $resumo['custo_por_unidade_consumo'], 0.000001);
    }

    public function test_legacy_invalid_equivalent_purchase_and_subunit_keeps_correct_purchase_cost(): void
    {
        $insumo = new Insumo([
            'nome' => 'Tinta Pigmentada TJet 4L',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1,
            'quantidade_subunidades_por_compra' => 4,
            'unidade_subunidade' => 'frascos',
            'quantidade_consumo_por_subunidade' => 1000,
            'custo_medio' => 0.15,
            'custo_unitario_consumo' => 0.15,
        ]);

        $this->assertTrue($insumo->temConversaoUnidade());
        $this->assertFalse($insumo->temDoisNiveisConversao());
        $this->assertEqualsWithDelta(1000.0, $insumo->getFatorTotalConversao(), 0.000001);
        $this->assertEqualsWithDelta(150.0, $insumo->getCustoPorUnidadeCompraEfetivo(), 0.000001);

        $resumo = $insumo->getResumoConversaoCustos();
        $this->assertTrue($resumo['configuracao_compra_invalida']);
        $this->assertEqualsWithDelta(0.15, $resumo['custo_por_unidade_base'], 0.000001);
        $this->assertEqualsWithDelta(150.0, $resumo['custo_por_unidade_compra'], 0.000001);
    }

    public function test_legacy_purchase_cost_without_consumption_cost_is_normalized_by_real_factor(): void
    {
        $insumo = new Insumo([
            'nome' => 'Tinta Pigmentada TJet 4L',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1,
            'quantidade_subunidades_por_compra' => 4,
            'unidade_subunidade' => 'frascos',
            'quantidade_consumo_por_subunidade' => 1000,
            'custo_medio' => 150.0,
            'custo_unitario_consumo' => null,
        ]);

        $this->assertEqualsWithDelta(0.15, $insumo->getCustoEfetivo(), 0.000001);
        $this->assertEqualsWithDelta(150.0, $insumo->getCustoPorUnidadeCompraEfetivo(), 0.000001);
    }
}
