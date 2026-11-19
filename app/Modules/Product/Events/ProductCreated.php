<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class ProductCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $productId,
        public string $sku,
        public string $name,
        public int $unitId,
        public float $minStock,
    ) {}
}
