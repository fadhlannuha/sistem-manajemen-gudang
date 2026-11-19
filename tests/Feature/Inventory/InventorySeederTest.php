<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Inventory\Database\Seeders\InventorySeeder;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_seeder_creates_demo_documents_and_balances(): void
    {
        $this->seed();

        $this->assertSame(20, Product::query()->count());
        $this->assertSame(20, StockBalance::query()->count());
        $this->assertSame(3, StockReceipt::query()->count());
        $this->assertSame(2, StockIssue::query()->count());
        $this->assertSame(16, StockReceipt::query()->withCount('items')->get()->sum('items_count'));
        $this->assertSame(7, StockIssue::query()->withCount('items')->get()->sum('items_count'));
        $this->assertSame(23, StockMovement::query()->count());

        $numbers = StockReceipt::query()->orderBy('id')->pluck('number')->all();
        $this->assertMatchesRegularExpression('/^SR-\d{6}-\d{6}$/', (string) $numbers[0]);

        foreach (StockIssue::query()->orderBy('id')->pluck('number') as $number) {
            $this->assertMatchesRegularExpression('/^SI-\d{6}-\d{6}$/', (string) $number);
        }

        $prd0005 = Product::query()->where('sku', 'PRD-0005')->firstOrFail();
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $prd0005->id,
            'qty' => 4,
        ]);

        $this->assertSame(0, StockBalance::query()->where('qty', '<', 0)->count());
    }

    public function test_inventory_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed(InventorySeeder::class);

        $this->assertSame(3, StockReceipt::query()->count());
        $this->assertSame(2, StockIssue::query()->count());
        $this->assertSame(20, StockBalance::query()->count());
        $this->assertSame(23, StockMovement::query()->count());
    }
}
