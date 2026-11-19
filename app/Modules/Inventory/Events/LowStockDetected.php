<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class LowStockDetected implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $productId,
        public string $productName,
        public float $currentQty,
        public float $minStock,
        public string $detectedAt,
    ) {}
}
