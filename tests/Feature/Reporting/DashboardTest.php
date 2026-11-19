<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Modules\Auth\Database\Seeders\RoleSeeder;
use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Product\Models\Product;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_dashboard_shows_counts(): void
    {
        $staff = User::factory()->staff()->create();

        $productA = Product::factory()->create(['min_stock' => 0]);
        $productB = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $productA->id, 'qty' => 50]);
        StockBalance::create(['product_id' => $productB->id, 'qty' => 50]);

        PaymentTerm::factory()->create();
        Supplier::factory()->create();

        $response = $this->actingAs($staff)->get(route('dashboard.index'));

        $response->assertOk();
        $response->assertViewHas('productCount', 2);
        $response->assertViewHas('supplierCount', 1);
        $response->assertViewHas('lowStockCount', 0);
        $response->assertSee('Total Barang');
        $response->assertSee('Total Pemasok');
    }

    public function test_dashboard_shows_low_stock(): void
    {
        $staff = User::factory()->staff()->create();

        $low = Product::factory()->create(['name' => 'Barang Dashboard Rendah', 'min_stock' => 10]);
        $safe = Product::factory()->create(['name' => 'Barang Dashboard Aman', 'min_stock' => 5]);

        StockBalance::create(['product_id' => $low->id, 'qty' => 2]);
        StockBalance::create(['product_id' => $safe->id, 'qty' => 50]);

        $response = $this->actingAs($staff)->get(route('dashboard.index'));

        $response->assertOk();
        $response->assertViewHas('lowStockCount', 1);
        $response->assertSee('Barang Dashboard Rendah');
        $response->assertDontSee('Barang Dashboard Aman');
    }

    public function test_dashboard_shows_recent_movement(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'movement_type' => 'receipt',
            'movement_date' => '2026-01-05',
            'qty_in' => 10,
            'qty_out' => 0,
            'balance_after' => 10,
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard.index'));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee('Masuk');
        $response->assertSee('10,000');
    }

    public function test_dashboard_does_not_expose_other_modules_models(): void
    {
        $reportingFiles = [
            base_path('app/Modules/Reporting/Services/DashboardService.php'),
            base_path('app/Modules/Reporting/Listeners/RecordActivityLog.php'),
            base_path('app/Modules/Reporting/Http/Controllers/DashboardController.php'),
            base_path('app/Modules/Reporting/Http/Controllers/ActivityController.php'),
        ];

        $forbiddenImports = [
            'App\\Modules\\Product\\Models',
            'App\\Modules\\Supplier\\Models',
            'App\\Modules\\Inventory\\Models',
            'App\\Modules\\Auth\\Models',
        ];

        foreach ($reportingFiles as $file) {
            $source = (string) file_get_contents($file);

            foreach ($forbiddenImports as $forbidden) {
                $this->assertStringNotContainsString(
                    'use '.$forbidden,
                    $source,
                    basename($file).' tidak boleh mengimpor model modul lain.',
                );
            }
        }
    }
}
