<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Product\Events\ProductCreated;

final class CreateStockBalance
{
    public function handle(ProductCreated $event): void
    {
        StockBalance::query()->firstOrCreate(
            ['product_id' => $event->productId],
            ['qty' => 0],
        );
    }
}
