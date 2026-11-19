<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\StockIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockIssue>
 */
class StockIssueFactory extends Factory
{
    protected $model = StockIssue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'SI-'.now()->format('Ym').'-'.fake()->unique()->numerify('######'),
            'issued_at' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'destination' => fake()->optional()->words(3, true),
            'created_by' => fake()->numberBetween(1, 50),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
