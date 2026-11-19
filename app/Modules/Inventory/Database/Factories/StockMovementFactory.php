<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => fake()->numberBetween(1, 50),
            'movement_type' => fake()->randomElement(['receipt', 'issue', 'adjustment']),
            'reference_type' => null,
            'reference_id' => null,
            'movement_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'qty_in' => 0,
            'qty_out' => 0,
            'balance_after' => fake()->randomFloat(3, 0, 1000),
            'unit_cost' => fake()->optional()->randomFloat(2, 1000, 100000),
            'created_by' => fake()->numberBetween(1, 50),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
