<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTO;

final readonly class StockMovementView
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $type,
        public ?string $referenceType,
        public ?int $referenceId,
        public ?string $referenceNumber,
        public string $movementDate,
        public float $qtyIn,
        public float $qtyOut,
        public float $balanceAfter,
        public ?float $unitCost = null,
        public ?string $note = null,
    ) {}
}
