<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class StockReceived implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param  array<int, array{product_id: int, qty: float, unit_cost: float}>  $items
     */
    public function __construct(
        public int $receiptId,
        public string $number,
        public int $supplierId,
        public array $items,
        public string $receivedAt,
        public int $actorId,
    ) {}
}
