<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderHistory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMovedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ProductionOrder $order,
        public readonly ProductionOrderHistory $history,
        public readonly int $lojaId,
        public readonly ?int $usuarioId = null
    ) {
    }
}
