<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\StockReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockReceipt>
 */
class StockReceiptFactory extends Factory
{
    protected $model = StockReceipt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'SR-'.now()->format('Ym').'-'.fake()->unique()->numerify('######'),
            'supplier_id' => fake()->numberBetween(1, 50),
            'received_at' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'total_amount' => 0,
            'created_by' => fake()->numberBetween(1, 50),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
