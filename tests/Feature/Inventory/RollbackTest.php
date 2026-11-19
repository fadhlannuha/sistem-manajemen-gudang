<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_item_failure_rolls_back_first_item(): void
    {
        $staff = User::factory()->staff()->create();

        $productA = Product::factory()->create(['min_stock' => 0]);
        $productB = Product::factory()->create(['min_stock' => 0]);

        $this->assertLessThan($productB->id, $productA->id);

        StockBalance::create(['product_id' => $productA->id, 'qty' => 10]);
        StockBalance::create(['product_id' => $productB->id, 'qty' => 1]);

        $response = $this->actingAs($staff)
            ->from(route('inventory.issues.create'))
            ->post(route('inventory.issues.store'), [
                'issued_at' => '2026-01-06',
                'items' => [
                    ['product_id' => $productA->id, 'qty' => 2],
                    ['product_id' => $productB->id, 'qty' => 5],
                ],
            ]);

        $response->assertRedirect(route('inventory.issues.create'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('stock_issues', 0);
        $this->assertDatabaseCount('stock_issue_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);

        $this->assertDatabaseHas('stock_balances', ['product_id' => $productA->id, 'qty' => 10]);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $productB->id, 'qty' => 1]);
    }
}
