<?php

declare(strict_types=1);

namespace App\Modules\Product\Database\Factories;

use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->numerify('PRD-####'),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'unit_id' => Unit::factory(),
            'min_stock' => fake()->randomFloat(3, 0, 100),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product): void {
            if ($product->categories()->exists()) {
                return;
            }

            $categories = Category::query()->inRandomOrder()->limit(2)->pluck('id');

            if ($categories->isEmpty()) {
                $categories = Category::factory()->count(2)->create()->pluck('id');
            }

            $product->categories()->syncWithoutDetaching($categories->all());
        });
    }
}
