<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Events\StockIssued;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Models\Product;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class IssueStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_decreases_balance_and_creates_movement(): void
    {
        Event::fake([StockIssued::class]);

        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        $response = $this->actingAs($staff)->post(route('inventory.issues.store'), [
            'issued_at' => '2026-01-06',
            'destination' => 'Divisi Umum',
            'note' => 'Pemakaian kantor',
            'items' => [
                ['product_id' => $product->id, 'qty' => 3],
            ],
        ]);

        $issue = StockIssue::query()->firstOrFail();

        $response->assertRedirect(route('inventory.issues.show', $issue));

        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 7]);
        $this->assertDatabaseCount('stock_issue_items', 1);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'issue',
            'reference_type' => 'stock_issue',
            'reference_id' => $issue->id,
            'qty_in' => 0,
            'qty_out' => 3,
            'balance_after' => 7,
        ]);

        Event::assertDispatched(
            StockIssued::class,
            fn (StockIssued $event): bool => $event->issueId === $issue->id
                && $event->number === $issue->number
                && count($event->items) === 1,
        );
    }

    public function test_issue_number_format(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        $this->actingAs($staff)->post(route('inventory.issues.store'), [
            'issued_at' => '2026-01-06',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
        ]);

        $issue = StockIssue::query()->firstOrFail();

        $this->assertSame('SI-202601-000001', (string) $issue->number);
        $this->assertMatchesRegularExpression('/^SI-\d{6}-\d{6}$/', (string) $issue->number);
    }

    public function test_stock_issue_is_immutable(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        $this->actingAs($staff)->post(route('inventory.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-05',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000],
            ],
        ]);

        $this->actingAs($staff)->post(route('inventory.issues.store'), [
            'issued_at' => '2026-01-06',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
        ]);

        $receipt = StockReceipt::query()->firstOrFail();
        $issue = StockIssue::query()->firstOrFail();

        $this->actingAs($staff)->get("/inventory/receipts/{$receipt->id}/edit")->assertNotFound();
        $this->actingAs($staff)->delete("/inventory/receipts/{$receipt->id}")->assertMethodNotAllowed();

        $this->actingAs($staff)->get("/inventory/issues/{$issue->id}/edit")->assertNotFound();
        $this->actingAs($staff)->delete("/inventory/issues/{$issue->id}")->assertMethodNotAllowed();

        $this->assertDatabaseCount('stock_receipts', 1);
        $this->assertDatabaseCount('stock_issues', 1);
    }

    public function test_api_requires_token(): void
    {
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        $this->postJson('/api/v1/inventory/issues', [
            'issued_at' => '2026-01-06',
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
        ])->assertUnauthorized();

        $this->assertDatabaseCount('stock_issues', 0);
    }
}
