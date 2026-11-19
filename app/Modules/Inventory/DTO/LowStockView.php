<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTO;

final readonly class LowStockView
{
    public function __construct(
        public int $productId,
        public float $qty,
        public float $minStock,
    ) {}
}
