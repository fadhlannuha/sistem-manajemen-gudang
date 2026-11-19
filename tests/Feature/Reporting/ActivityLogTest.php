<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Modules\Auth\Database\Seeders\RoleSeeder;
use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use App\Modules\Reporting\Models\ActivityLog;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_product_created_is_logged(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)->post(route('products.store'), [
            'sku' => 'PRD-9001',
            'name' => 'Produk Aktivitas',
            'unit_id' => $unit->id,
            'min_stock' => 3,
        ]);

        $product = Product::query()->where('sku', 'PRD-9001')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'product.created',
            'subject_type' => 'product',
            'subject_id' => $product->id,
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
        ]);

        $log = ActivityLog::query()->where('event', 'product.created')->firstOrFail();

        $this->assertStringContainsString('Produk Aktivitas', $log->description);
        $this->assertStringContainsString('PRD-9001', $log->description);
        $this->assertSame('PRD-9001', $log->payload['sku']);
    }

    public function test_stock_issued_is_logged_with_number(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 10]);

        $this->actingAs($staff)->post(route('inventory.issues.store'), [
            'issued_at' => '2026-01-06',
            'destination' => 'Divisi Umum',
            'items' => [
                ['product_id' => $product->id, 'qty' => 3],
            ],
        ]);

        $issue = StockIssue::query()->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'stock.issued',
            'subject_type' => 'stock_issue',
            'subject_id' => $issue->id,
            'actor_id' => $staff->id,
            'actor_name' => $staff->name,
        ]);

        $log = ActivityLog::query()->where('event', 'stock.issued')->firstOrFail();

        $this->assertStringContainsString((string) $issue->number, $log->description);
        $this->assertSame((string) $issue->number, $log->payload['number']);
        $this->assertEquals(3.0, $log->payload['total_qty']);
    }

    public function test_user_registration_is_logged(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Pendaftar Baru',
            'email' => 'pendaftar@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'user.registered',
            'subject_type' => 'user',
            'actor_name' => 'Pendaftar Baru',
        ]);
    }

    public function test_stock_received_is_logged(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['min_stock' => 0]);
        StockBalance::create(['product_id' => $product->id, 'qty' => 0]);

        $this->actingAs($staff)->post(route('inventory.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => '2026-01-05',
            'items' => [
                ['product_id' => $product->id, 'qty' => 5, 'unit_cost' => 1000],
            ],
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'stock.received',
            'subject_type' => 'stock_receipt',
            'actor_id' => $staff->id,
        ]);
    }

    public function test_activity_page_is_admin_only(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($staff)->get(route('activity.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('activity.index'))->assertOk();
    }

    public function test_activity_page_can_be_filtered(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::factory()->create([
            'event' => 'product.created',
            'description' => 'Log produk',
            'actor_name' => 'Admin Satu',
            'created_at' => '2026-01-10 08:00:00',
        ]);
        ActivityLog::factory()->create([
            'event' => 'stock.issued',
            'description' => 'Log stok',
            'actor_name' => 'Staff Dua',
            'created_at' => '2026-01-11 09:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('activity.index', [
            'event' => 'product.created',
        ]));

        $response->assertOk();
        $response->assertSee('Log produk');
        $response->assertDontSee('Log stok');

        $actorFilter = $this->actingAs($admin)->get(route('activity.index', [
            'actor' => 'Staff Dua',
        ]));

        $actorFilter->assertOk();
        $actorFilter->assertSee('Log stok');
        $actorFilter->assertDontSee('Log produk');
    }

    public function test_activity_page_rejects_invalid_date_range(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('activity.index'))
            ->get(route('activity.index', [
                'from' => '2026-02-01',
                'to' => '2026-01-01',
            ]))
            ->assertRedirect(route('activity.index'))
            ->assertSessionHasErrors('to');
    }
}
