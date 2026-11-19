<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Models\Product;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_card_lists_movements_chronologically(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 7]);

        $supplier = Supplier::factory()->create();
        $receipt = StockReceipt::factory()->create([
            'number' => 'SR-202601-000099',
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-01',
            'total_amount' => 50000,
            'created_by' => $staff->id,
        ]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'movement_type' => 'receipt',
            'reference_type' => 'stock_receipt',
            'reference_id' => $receipt->id,
            'movement_date' => '2026-01-01',
            'qty_in' => 10,
            'qty_out' => 0,
            'balance_after' => 10,
            'created_by' => $staff->id,
        ]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'movement_type' => 'issue',
            'movement_date' => '2026-01-02',
            'qty_in' => 0,
            'qty_out' => 3,
            'balance_after' => 7,
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('inventory.stock-card.index', [
            'product_id' => $product->id,
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['01-01-2026', '02-01-2026']);
        $response->assertSee('10,000');
        $response->assertSee('7,000');
        $response->assertSee('Masuk');
        $response->assertSee('Keluar');
        $response->assertSee('SR-202601-000099');
    }

    public function test_stock_card_can_filter_by_date(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        foreach (['2026-01-01', '2026-01-05', '2026-01-10'] as $date) {
            StockMovement::factory()->create([
                'product_id' => $product->id,
                'movement_type' => 'receipt',
                'movement_date' => $date,
                'qty_in' => 1,
                'qty_out' => 0,
                'balance_after' => 1,
                'created_by' => $staff->id,
            ]);
        }

        $response = $this->actingAs($staff)->get(route('inventory.stock-card.index', [
            'product_id' => $product->id,
            'from' => '2026-01-04',
            'to' => '2026-01-06',
        ]));

        $response->assertOk();
        $response->assertSee('05-01-2026');
        $response->assertDontSee('01-01-2026');
        $response->assertDontSee('10-01-2026');
    }

    public function test_staff_can_view_stock_card(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get(route('inventory.stock-card.index'));

        $response->assertOk();
        $response->assertSee('Pilih barang terlebih dahulu');
    }

    public function test_stock_card_remains_readable_for_deactivated_product(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 2]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'movement_type' => 'receipt',
            'movement_date' => '2026-01-01',
            'qty_in' => 2,
            'qty_out' => 0,
            'balance_after' => 2,
            'created_by' => $staff->id,
        ]);

        $product->delete();

        $response = $this->actingAs($staff)->get(route('inventory.stock-card.index', [
            'product_id' => $product->id,
        ]));

        $response->assertOk();
        $response->assertSee((string) $product->sku);
    }
}
