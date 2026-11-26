<?php

declare(strict_types=1);

namespace Tests\Feature\Supplier;

use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\Database\Seeders\SupplierSeeder;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupplierSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_seeder_creates_master_data(): void
    {
        $this->seed(SupplierSeeder::class);

        $this->assertSame(4, PaymentTerm::query()->count());
        $this->assertSame(5, Supplier::query()->count());

        $this->assertDatabaseHas('payment_terms', ['name' => 'Cash', 'days' => 0]);
        $this->assertDatabaseHas('payment_terms', ['name' => 'Net 30', 'days' => 30]);
        $this->assertDatabaseHas('suppliers', ['code' => 'SUP-001', 'name' => 'PT Sumber Elektronik Nusantara']);
        $this->assertDatabaseHas('suppliers', ['code' => 'SUP-005', 'name' => 'CV Kemas Jaya']);

        $supplier = Supplier::query()->where('code', 'SUP-001')->with('paymentTerm')->firstOrFail();
        $this->assertSame('Net 30', $supplier->paymentTerm->name);
    }

    public function test_supplier_seeder_is_idempotent(): void
    {
        $this->seed(SupplierSeeder::class);
        $this->seed(SupplierSeeder::class);

        $this->assertSame(4, PaymentTerm::query()->count());
        $this->assertSame(5, Supplier::query()->count());
    }

    public function test_database_seeder_includes_suppliers(): void
    {
        $this->seed();

        $this->assertSame(4, PaymentTerm::query()->count());
        $this->assertSame(5, Supplier::query()->count());
    }

    public function test_supplier_seeder_restores_deactivated_suppliers(): void
    {
        $this->seed(SupplierSeeder::class);

        Supplier::query()->where('code', 'SUP-001')->firstOrFail()->delete();

        $this->assertSame(4, Supplier::query()->count());

        $this->seed(SupplierSeeder::class);

        $this->assertSame(5, Supplier::query()->count());
        $this->assertNotSoftDeleted('suppliers', ['code' => 'SUP-001']);
    }

    public function test_supplier_factory_can_create_deactivated_supplier(): void
    {
        $supplier = Supplier::factory()->deactivated()->create();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
        $this->assertNull($this->app->make(SupplierDirectoryContract::class)->find($supplier->id));
    }

    public function test_payment_term_factory_uses_documented_day_range(): void
    {
        foreach (PaymentTerm::factory()->count(30)->make() as $paymentTerm) {
            $this->assertGreaterThanOrEqual(0, $paymentTerm->days);
            $this->assertLessThanOrEqual(30, $paymentTerm->days);
        }
    }

    public function test_suppliers_table_has_indexes_required_by_database_documentation(): void
    {
        $indexes = collect(DB::select("PRAGMA index_list('suppliers')"))
            ->pluck('name')
            ->all();

        $this->assertContains('suppliers_deleted_at_index', $indexes);
        $this->assertContains('suppliers_name_index', $indexes);
        $this->assertContains('suppliers_payment_term_id_index', $indexes);
    }
}
