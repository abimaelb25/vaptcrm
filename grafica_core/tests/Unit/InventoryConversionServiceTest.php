<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Insumo;
use App\Services\Domain\InventoryConversionService;
use Tests\TestCase;

final class InventoryConversionServiceTest extends TestCase
{
    public function test_validate_conversion_structure_for_simple_conversion(): void
    {
        $service = new InventoryConversionService();

        $normalized = $service->validateConversionStructure([
            'unidade_medida' => 'folha',
            'unidade_compra' => 'pacote',
            'quantidade_por_compra' => 40,
            'unidade_subunidade' => '',
            'quantidade_subunidades_por_compra' => '',
            'quantidade_consumo_por_subunidade' => '',
        ]);

        $this->assertSame('pacote', $normalized['unidade_compra']);
        $this->assertSame(40, $normalized['quantidade_por_compra']);
        $this->assertNull($normalized['unidade_subunidade']);
        $this->assertNull($normalized['quantidade_subunidades_por_compra']);
        $this->assertNull($normalized['quantidade_consumo_por_subunidade']);
    }

    public function test_validate_conversion_structure_for_two_levels(): void
    {
        $service = new InventoryConversionService();

        $normalized = $service->validateConversionStructure([
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 600,
            'unidade_subunidade' => 'frasco',
            'quantidade_subunidades_por_compra' => 6,
            'quantidade_consumo_por_subunidade' => 100,
        ]);

        $this->assertSame('caixa', $normalized['unidade_compra']);
        $this->assertSame('frasco', $normalized['unidade_subunidade']);
        $this->assertSame(6, $normalized['quantidade_subunidades_por_compra']);
        $this->assertSame(100, $normalized['quantidade_consumo_por_subunidade']);
    }

    public function test_get_packaging_summary_for_two_levels(): void
    {
        $service = new InventoryConversionService();

        $insumo = new Insumo([
            'unidade_medida' => 'ml',
            'unidade_compra' => 'caixa',
            'quantidade_por_compra' => 600,
            'unidade_subunidade' => 'frascos',
            'quantidade_subunidades_por_compra' => 6,
            'quantidade_consumo_por_subunidade' => 100,
        ]);

        $summary = $service->getPackagingSummary($insumo);

        $this->assertTrue($summary['has_conversion']);
        $this->assertTrue($summary['has_two_levels']);
        $this->assertEqualsWithDelta(600.0, $summary['factor_total'], 0.0001);
        $this->assertStringContainsString('caixa', $summary['description']);
    }
}
