<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Domain\InventoryCostService;
use Tests\TestCase;

final class InventoryCostServiceTest extends TestCase
{
    public function test_cost_math_and_rounding_precision(): void
    {
        $service = new InventoryCostService();

        $costPerBase = $service->calculateConsumptionCostFromPurchase(58.65, 40);
        $this->assertEqualsWithDelta(1.46625, $costPerBase, 0.000001);

        $costPerPurchase = $service->calculatePurchaseCostFromConsumption(0.0833, 600);
        $this->assertEqualsWithDelta(49.98, $costPerPurchase, 0.000001);

        $avg = $service->calculateUpdatedAverageCost(100, 1.2, 50, 1.5);
        $this->assertEqualsWithDelta(1.3, $avg, 0.000001);

        $this->assertSame(1.46625, $service->roundMoney(1.4662499, 5));
    }
}
