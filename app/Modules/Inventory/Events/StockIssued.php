<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class StockIssued implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     */
    public function __construct(
        public int $issueId,
        public string $number,
        public array $items,
        public string $issuedAt,
        public int $actorId,
    ) {}
}
