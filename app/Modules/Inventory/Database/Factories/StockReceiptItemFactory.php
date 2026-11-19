<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Inventory\Models\StockReceiptItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockReceiptItem>
 */
class StockReceiptItemFactory extends Factory
{
    protected $model = StockReceiptItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_receipt_id' => StockReceipt::factory(),
            'product_id' => fake()->numberBetween(1, 50),
            'qty' => fake()->randomFloat(3, 1, 100),
            'unit_cost' => fake()->randomFloat(2, 1000, 100000),
            'subtotal' => fake()->randomFloat(2, 1000, 1000000),
        ];
    }
}
