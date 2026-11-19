<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Factories;

use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockIssueItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockIssueItem>
 */
class StockIssueItemFactory extends Factory
{
    protected $model = StockIssueItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_issue_id' => StockIssue::factory(),
            'product_id' => fake()->numberBetween(1, 50),
            'qty' => fake()->randomFloat(3, 1, 100),
        ];
    }
}
