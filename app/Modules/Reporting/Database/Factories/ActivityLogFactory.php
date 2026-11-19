<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Database\Factories;

use App\Modules\Reporting\Models\ActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => null,
            'actor_name' => fake()->name(),
            'event' => fake()->randomElement([
                'user.registered',
                'product.created',
                'product.updated',
                'supplier.created',
                'stock.received',
                'stock.issued',
                'stock.low',
            ]),
            'subject_type' => fake()->randomElement(['product', 'supplier', 'stock_receipt', 'stock_issue']),
            'subject_id' => fake()->numberBetween(1, 100),
            'description' => fake()->sentence(),
            'payload' => ['source' => 'factory'],
            'created_at' => now(),
        ];
    }
}
