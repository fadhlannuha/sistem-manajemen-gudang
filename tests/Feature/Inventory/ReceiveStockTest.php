<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Events\LowStockDetected;
use App\Modules\Inventory\Events\StockReceived;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Models\Product;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReceiveStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_increases_balance_and_creates_movement(): void
    {
        Event::fake([StockReceived::class, LowStockDetected::class]);

        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();

        $productA = Product::factory()->create(['min_stock' => 0]);
        $productB = Product::factory()->create(['min_stock' => 0]);

        StockBalance::create(['product_id' => $productA->id, 'qty' => 0]);
        StockBalance::create(['product_id' => $productB->id, 'qty' => 0]);

        $response = $this->actingAs($staff)->post(route('inventory.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-05',
            'note' => 'Penerimaan awal',
            'items' => [
                ['product_id' => $productA->id, 'qty' => 10, 'unit_cost' => 5000],
                ['product_id' => $productB->id, 'qty' => 5, 'unit_cost' => 2000],
            ],
        ]);

        $receipt = StockReceipt::query()->firstOrFail();

        $response->assertRedirect(route('inventory.receipts.show', $receipt));

        $this->assertDatabaseHas('stock_balances', ['product_id' => $productA->id, 'qty' => 10]);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $productB->id, 'qty' => 5]);
        $this->assertDatabaseCount('stock_receipt_items', 2);
        $this->assertDatabaseCount('stock_movements', 2);

        $this->assertSame('60000.00', (string) $receipt->total_amount);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productA->id,
            'movement_type' => 'receipt',
            'reference_type' => 'stock_receipt',
            'reference_id' => $receipt->id,
            'qty_in' => 10,
            'qty_out' => 0,
            'balance_after' => 10,
        ]);

        Event::assertDispatched(
            StockReceived::class,
            fn (StockReceived $event): bool => $event->receiptId === $receipt->id
                && $event->number === $receipt->number
                && $event->supplierId === $supplier->id
                && count($event->items) === 2,
        );
    }

    public function test_receipt_number_format(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 0]);

        StockBalance::create(['product_id' => $product->id, 'qty' => 0]);

        $this->actingAs($staff)->post(route('inventory.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-05',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000],
            ],
        ]);

        $receipt = StockReceipt::query()->firstOrFail();

        $this->assertSame('SR-202601-000001', (string) $receipt->number);
        $this->assertMatchesRegularExpression('/^SR-\d{6}-\d{6}$/', (string) $receipt->number);
    }

    public function test_receipt_requires_at_least_one_item(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($staff)
            ->from(route('inventory.receipts.create'))
            ->post(route('inventory.receipts.store'), [
                'supplier_id' => $supplier->id,
                'received_at' => '2026-01-05',
                'items' => [],
            ]);

        $response->assertRedirect(route('inventory.receipts.create'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('stock_receipts', 0);
        $this->assertDatabaseCount('stock_receipt_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_receipt_rejects_supplier_unavailable(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 0]);

        $response = $this->actingAs($staff)
            ->from(route('inventory.receipts.create'))
            ->post(route('inventory.receipts.store'), [
                'supplier_id' => $supplier->id,
                'received_at' => '2026-01-05',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000],
                ],
            ]);

        $response->assertRedirect(route('inventory.receipts.create'));
        $response->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseCount('stock_receipts', 0);
        $this->assertDatabaseCount('stock_receipt_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 0]);
    }

    public function test_receipt_rejects_product_unavailable(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 0]);
        $product->delete();

        $response = $this->actingAs($staff)
            ->from(route('inventory.receipts.create'))
            ->post(route('inventory.receipts.store'), [
                'supplier_id' => $supplier->id,
                'received_at' => '2026-01-05',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000],
                ],
            ]);

        $response->assertRedirect(route('inventory.receipts.create'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('stock_receipts', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_low_stock_event_dispatched(): void
    {
        Event::fake([StockReceived::class, LowStockDetected::class]);

        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 5, 'name' => 'Barang Ambang']);

        StockBalance::create(['product_id' => $product->id, 'qty' => 4]);

        $this->actingAs($staff)->post(route('inventory.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-05',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000],
            ],
        ]);

        Event::assertDispatched(
            LowStockDetected::class,
            fn (LowStockDetected $event): bool => $event->productId === $product->id
                && $event->productName === 'Barang Ambang'
                && $event->currentQty === 5.0
                && $event->minStock === 5.0,
        );
    }

    public function test_api_receipt_returns_201(): void
    {
        $staff = User::factory()->staff()->create([
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ]);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 0]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ])->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/inventory/receipts', [
                'supplier_id' => $supplier->id,
                'received_at' => '2026-01-05',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 2, 'unit_cost' => 1500],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.number', 'SR-202601-000001')
            ->assertJsonStructure(['data' => ['id', 'number']]);

        $this->assertDatabaseCount('stock_receipts', 1);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 2]);
    }
}
