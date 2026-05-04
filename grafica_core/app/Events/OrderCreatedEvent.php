<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ProductionOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ProductionOrder $order,
        public readonly int $lojaId,
        public readonly ?int $usuarioId = null
    ) {
    }
}
