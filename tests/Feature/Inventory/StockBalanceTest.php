<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Listeners\CreateStockBalance;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_never_negative(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 5]);

        $this->actingAs($staff)
            ->from(route('inventory.issues.create'))
            ->post(route('inventory.issues.store'), [
                'issued_at' => '2026-01-06',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 8],
                ],
            ])
            ->assertSessionHasErrors('items');

        $balance = StockBalance::query()->where('product_id', $product->id)->firstOrFail();

        $this->assertSame('5.000', (string) $balance->qty);
        $this->assertGreaterThanOrEqual(0, (float) $balance->qty);
    }

    public function test_balance_page_lists_products_and_flags_low_stock(): void
    {
        $staff = User::factory()->staff()->create();

        $safe = Product::factory()->create(['name' => 'Barang Aman', 'min_stock' => 5]);
        $low = Product::factory()->create(['name' => 'Barang Rendah', 'min_stock' => 10]);

        StockBalance::create(['product_id' => $safe->id, 'qty' => 20]);
        StockBalance::create(['product_id' => $low->id, 'qty' => 2]);

        $response = $this->actingAs($staff)->get(route('inventory.balances.index'));

        $response->assertOk();
        $response->assertSee('Barang Aman');
        $response->assertSee('Barang Rendah');
        $response->assertSee('Aman');
        $response->assertSee('Rendah');

        $lowOnly = $this->actingAs($staff)->get(route('inventory.balances.index', [
            'low_stock' => 1,
        ]));

        $lowOnly->assertOk();
        $lowOnly->assertSee('Barang Rendah');
        $lowOnly->assertDontSee('Barang Aman');
    }

    public function test_create_stock_balance_listener_creates_zero_balance(): void
    {
        $product = Product::factory()->create();

        $listener = new CreateStockBalance;
        $event = new ProductCreated(
            productId: $product->id,
            sku: (string) $product->sku,
            name: (string) $product->name,
            unitId: $product->unit_id,
            minStock: (float) $product->min_stock,
        );

        $listener->handle($event);
        $listener->handle($event);

        $this->assertDatabaseCount('stock_balances', 1);
        $this->assertDatabaseHas('stock_balances', [
            'product_id' => $product->id,
            'qty' => 0,
        ]);
    }
}
