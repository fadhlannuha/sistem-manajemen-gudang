<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Modules\Product\Database\Seeders\ProductSeeder;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seeder_creates_master_data(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertSame(5, Category::query()->count());
        $this->assertSame(5, Unit::query()->count());
        $this->assertSame(20, Product::query()->count());

        $this->assertDatabaseHas('categories', ['name' => 'Elektronik', 'slug' => 'elektronik']);
        $this->assertDatabaseHas('categories', ['name' => 'ATK', 'slug' => 'atk']);
        $this->assertDatabaseHas('units', ['name' => 'Piece', 'code' => 'PCS']);
        $this->assertDatabaseHas('units', ['name' => 'Roll', 'code' => 'ROLL']);
        $this->assertDatabaseHas('products', ['sku' => 'PRD-0001', 'name' => 'Kertas HVS A4 80gsm']);

        $multiCategoryProduct = Product::query()
            ->where('sku', 'PRD-0009')
            ->with('categories')
            ->firstOrFail();

        $this->assertCount(2, $multiCategoryProduct->categories);
        $this->assertDatabaseCount('category_product', 23);
    }

    public function test_product_seeder_is_idempotent(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);

        $this->assertSame(5, Category::query()->count());
        $this->assertSame(5, Unit::query()->count());
        $this->assertSame(20, Product::query()->count());
        $this->assertDatabaseCount('category_product', 23);
    }

    public function test_database_seeder_includes_products(): void
    {
        $this->seed();

        $this->assertSame(20, Product::query()->count());
        $this->assertDatabaseCount('category_product', 23);
    }
}
