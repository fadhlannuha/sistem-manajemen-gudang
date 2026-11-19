<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTO;

final readonly class StockBalanceView
{
    public function __construct(
        public int $productId,
        public float $qty,
        public ?string $updatedAt = null,
    ) {}
}
