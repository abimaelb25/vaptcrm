<?php

declare(strict_types=1);

namespace App\Services\Domain;

class InventoryCostService
{
    public function calculateConsumptionCostFromPurchase(float $purchaseUnitCost, float $factor): float
    {
        return $purchaseUnitCost / max(0.0001, $factor);
    }

    public function calculatePurchaseCostFromConsumption(float $consumptionUnitCost, float $factor): float
    {
        return $consumptionUnitCost * max(0.0001, $factor);
    }

    public function calculateUpdatedAverageCost(
        float $previousStock,
        float $previousAverageCost,
        float $entryQuantityBase,
        float $entryCostPerBase
    ): float {
        $newStock = $previousStock + $entryQuantityBase;

        if ($newStock <= 0) {
            return $entryCostPerBase;
        }

        return (($previousStock * $previousAverageCost) + ($entryQuantityBase * $entryCostPerBase)) / $newStock;
    }

    public function roundMoney(float $value, int $scale = 6): float
    {
        return round($value, $scale);
    }
}
