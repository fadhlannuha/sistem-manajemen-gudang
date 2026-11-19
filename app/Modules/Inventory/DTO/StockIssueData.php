<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTO;

final readonly class StockIssueData
{
    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    public function __construct(
        public string $issuedAt,
        public ?string $destination,
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
