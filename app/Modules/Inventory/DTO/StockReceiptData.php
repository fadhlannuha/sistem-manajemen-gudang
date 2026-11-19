<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTO;

final readonly class StockReceiptData
{
    /**
     * @param  array<int, array{product_id: int, qty: float, unit_cost: float}>  $items
     */
    public function __construct(
        public int $supplierId,
        public string $receivedAt,
        public ?string $note,
        public array $items,
    ) {}

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        return array_map(
            static fn (array $item): int => (int) $item['product_id'],
            $this->items,
        );
    }
}
