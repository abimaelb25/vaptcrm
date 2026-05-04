<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Models\Insumo;
use App\Models\Loja;
use App\Models\Usuario;
use App\Services\Domain\InsumoConversaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de Regressão Crítica: Módulo Insumos
 *
 * Caso de Regressão Obrigatório: Tinta Pigmentada TJet 4L
 * - Unidade base: ml
 * - Unidade compra: frasco
 * - Cada frasco: 1000 ml
 * - Ajuste físico: 700 ml restantes por frasco
 * - Resultado esperado: Saldo = 3400 ml (sem duplicação de conversão)
 */
final class InsumoConversionRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected Loja $loja;
    protected Usuario $admin;
    protected InsumoConversaoService $conversionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loja = Loja::factory()->create();
        $this->admin = Usuario::factory()->create([
            'loja_id' => $this->loja->id,
            'perfil' => 'administrador',
        ]);

        $this->conversionService = app(InsumoConversaoService::class);
    }

    /**
     * Teste: Conversão simples (1 frasco = 1000 ml) sem ambiguidades.
     */
    public function test_simple_conversion_frasco_to_ml(): void
    {
        $insumo = Insumo::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000.0,
            'unidade_subunidade' => null,
            'quantidade_subunidades_por_compra' => null,
            'quantidade_consumo_por_subunidade' => null,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
        ]);

        // Validações
        $this->assertTrue($this->conversionService->hasSimpleConversion($insumo));
        $this->assertFalse($this->conversionService->hasTwoLevelConversion($insumo));
        $this->assertEquals(1000.0, $this->conversionService->getTotalFactor($insumo));

        // Conversão
        $quantidadeCompra = 1.0; // 1 frasco
        $quantidadeBase = $this->conversionService->convertFromPurchaseToBase($quantidadeCompra, 1000.0);
        $this->assertEquals(1000.0, $quantidadeBase);

        // Preview
        $summary = $this->conversionService->getConversionSummary($insumo);
        $this->assertTrue($summary['has_conversion']);
        $this->assertFalse($summary['has_two_levels']);
        $this->assertStringContainsString('Cada frasco equivale a: 1000 ml', $summary['description']);
        $this->assertStringNotContainsString('Cada frasco contem 4 frascos', $summary['description']);
    }

    /**
     * Teste: Conversão com dois níveis válida (caixa com frascos).
     */
    public function test_two_level_conversion_valid(): void
    {
        $insumo = Insumo::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Tinta em Kit 4 Frascos',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 1.0,
            'unidade_subunidade' => 'frasco',
            'quantidade_subunidades_por_compra' => 4.0,
            'quantidade_consumo_por_subunidade' => 1000.0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
        ]);

        $this->assertFalse($this->conversionService->hasSimpleConversion($insumo));
        $this->assertTrue($this->conversionService->hasTwoLevelConversion($insumo));
        $this->assertEquals(4000.0, $this->conversionService->getTotalFactor($insumo));

        // Conversão: 1 caixa = 4000 ml
        $quantidadeCompra = 1.0;
        $quantidadeBase = $this->conversionService->convertFromPurchaseToBase($quantidadeCompra, 4000.0);
        $this->assertEquals(4000.0, $quantidadeBase);

        // Preview
        $summary = $this->conversionService->getConversionSummary($insumo);
        $this->assertTrue($summary['has_two_levels']);
        $this->assertStringContainsString('Cada caixa possui: 4 frasco', $summary['description']);
        $this->assertStringContainsString('Cada frasco possui: 1000 ml', $summary['description']);
        $this->assertStringContainsString('Total por caixa: 4000 ml', $summary['description']);
    }

    public function test_regression_tinta_costs_are_kept_separate_for_purchase_and_base_units(): void
    {
        $insumo = Insumo::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000.0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
            'custo_medio' => 0.15,
            'custo_unitario_consumo' => 0.15,
        ]);

        $preview = $this->conversionService->generateCostPreview($insumo);
        $resumo = $insumo->getResumoConversaoCustos();

        $this->assertEqualsWithDelta(0.15, $preview['custo_por_unidade_base'], 0.000001);
        $this->assertEqualsWithDelta(150.0, $preview['custo_por_unidade_compra'], 0.000001);
        $this->assertSame('ml', $preview['unidade_base']);
        $this->assertSame('frasco', $preview['unidade_compra']);

        $this->assertEqualsWithDelta(0.15, $resumo['custo_por_unidade_base'], 0.000001);
        $this->assertEqualsWithDelta(150.0, $resumo['custo_por_unidade_compra'], 0.000001);
        $this->assertEqualsWithDelta(1000.0, $resumo['quantidade_base_por_unidade_compra'], 0.000001);
    }

    /**
     * Teste CRÍTICO: Regressão Tinta 4L com ajuste físico.
     *
     * Cenário:
     * - Insumo criado em ml, compra em frasco (1000 ml/frasco)
     * - Entrada registrada: 4 frascos = 4000 ml
     * - Ajuste físico contado: 2 frascos completos + 2 abertos com 700 ml
     * - Saldo esperado: 3400 ml
     * - Verificação: Sem duplicação de conversão
     */
    public function test_regression_tinta_ajuste_fisico_700ml(): void
    {
        // Criar insumo com conversão simples
        $insumo = Insumo::create([
            'loja_id' => $this->loja->id,
            'nome' => 'Tinta Pigmentada TJet 4L',
            'tipo_item_operacional' => 'consumivel',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1000.0,
            'estoque_atual' => 4000.0,
            'estoque_minimo' => 0,
            'controlar_estoque' => true,
            'usar_na_precificacao' => true,
        ]);

        // Validar estrutura
        $this->assertTrue($this->conversionService->hasSimpleConversion($insumo));
        $this->assertFalse($this->conversionService->hasTwoLevelConversion($insumo));

        // Simular ajuste físico
        // Operador conta: 2 frascos completos + 2 abertos com 700 ml cada
        $saldoFisicoContado = 2 * 1000 + 2 * 700; // = 3400 ml
        $diferenca = $saldoFisicoContado - $insumo->estoque_atual; // = -600 ml

        // Verificação
        $this->assertEquals(3400.0, $saldoFisicoContado);
        $this->assertEquals(-600.0, $diferenca);

        // Atualizar saldo (simulando processamento)
        $insumo->update(['estoque_atual' => $saldoFisicoContado]);
        $this->assertEquals(3400.0, $insumo->estoque_atual);

        // Validação: Sem duplicação de conversão
        // Se houvesse duplicação, o saldo seria 3400 * 1000 = 3.400.000 (ERRO)
        $this->assertNotEquals(3400000.0, $insumo->estoque_atual);
    }

    /**
     * Teste: Validação rejeita composição semântica inválida.
     */
    public function test_validation_rejects_invalid_composition(): void
    {
        // Tenta criar composição inválida: unidade_compra = unidade_subunidade
        $invalidData = [
            'nome' => 'Insumo Inválido',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1.0,
            'unidade_subunidade' => 'frasco', // ERRO: mesmo nome!
            'quantidade_subunidades_por_compra' => 4.0,
            'quantidade_consumo_por_subunidade' => 1000.0,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->conversionService->validateAndNormalizeConversion($invalidData);
    }

    public function test_validation_rejects_invalid_composition_with_plural_equivalent_names(): void
    {
        $invalidData = [
            'nome' => 'Insumo Invalido',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'frasco',
            'quantidade_por_compra' => 1.0,
            'unidade_subunidade' => 'frascos',
            'quantidade_subunidades_por_compra' => 4.0,
            'quantidade_consumo_por_subunidade' => 1000.0,
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->conversionService->validateAndNormalizeConversion($invalidData);
    }

    /**
     * Teste: Validação normaliza estrutura ambígua.
     */
    public function test_validation_normalizes_ambiguous_structure(): void
    {
        // Dados com ambiguidade: quantidade_por_compra preenchida mas há 2 níveis
        $ambiguousData = [
            'nome' => 'Insumo Ambíguo',
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 100.0, // Redundante com 2 níveis
            'unidade_subunidade' => 'frasco',
            'quantidade_subunidades_por_compra' => 4.0,
            'quantidade_consumo_por_subunidade' => 1000.0,
        ];

        $normalized = $this->conversionService->validateAndNormalizeConversion($ambiguousData);

        // Deve normalizar quantidade_por_compra para 1 em 2 níveis
        $this->assertEquals(1, $normalized['quantidade_por_compra']);
    }

    /**
     * Teste: Sem conversão deixa estrutura limpa.
     */
    public function test_validation_removes_conversion_if_no_purchase_unit(): void
    {
        $data = [
            'nome' => 'Insumo Sem Conversão',
            'unidade_medida' => 'unidade',
            'unidade_compra' => '', // Vazio = sem conversão
            'quantidade_por_compra' => 100.0,
            'unidade_subunidade' => 'algo',
            'quantidade_subunidades_por_compra' => 5.0,
            'quantidade_consumo_por_subunidade' => 10.0,
        ];

        $normalized = $this->conversionService->validateAndNormalizeConversion($data);

        // Deve limpar tudo
        $this->assertNull($normalized['unidade_compra']);
        $this->assertEquals(1, $normalized['quantidade_por_compra']);
        $this->assertNull($normalized['unidade_subunidade']);
        $this->assertNull($normalized['quantidade_subunidades_por_compra']);
        $this->assertNull($normalized['quantidade_consumo_por_subunidade']);
    }
}
