<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\DTO\StockIssueData;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Services\IssueStockService;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InsufficientStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_throws_when_stock_is_not_enough(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0, 'name' => 'Kertas HVS']);
        StockBalance::create(['product_id' => $product->id, 'qty' => 5]);

        try {
            app(IssueStockService::class)->handle(
                new StockIssueData(
                    issuedAt: '2026-01-06',
                    destination: 'Divisi Umum',
                    note: null,
                    items: [
                        ['product_id' => $product->id, 'qty' => 10],
                    ],
                ),
                $staff->id,
            );

            $this->fail('InsufficientStockException tidak dilempar.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame($product->id, $exception->productId);
            $this->assertSame(10.0, $exception->requested);
            $this->assertSame(5.0, $exception->available);
            $this->assertStringContainsString('Kertas HVS', $exception->getMessage());
            $this->assertStringContainsString('5,000', $exception->getMessage());
            $this->assertStringContainsString('10,000', $exception->getMessage());
        }
    }

    public function test_no_data_is_saved_when_stock_is_insufficient(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 5]);

        $response = $this->actingAs($staff)
            ->from(route('inventory.issues.create'))
            ->post(route('inventory.issues.store'), [
                'issued_at' => '2026-01-06',
                'items' => [
                    ['product_id' => $product->id, 'qty' => 10],
                ],
            ]);

        $response->assertRedirect(route('inventory.issues.create'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('stock_issues', 0);
        $this->assertDatabaseCount('stock_issue_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 5]);
    }

    public function test_api_returns_409_with_code(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 5]);

        Sanctum::actingAs($staff);

        $response = $this->postJson('/api/v1/inventory/issues', [
            'issued_at' => '2026-01-06',
            'items' => [
                ['product_id' => $product->id, 'qty' => 10],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('code', 'INSUFFICIENT_STOCK')
            ->assertJsonPath('meta.product_id', $product->id)
            ->assertJsonPath('meta.available', 5)
            ->assertJsonPath('meta.requested', 10);

        $this->assertDatabaseCount('stock_issues', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'qty' => 5]);
    }
}
