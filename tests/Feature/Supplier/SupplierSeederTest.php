<?php

declare(strict_types=1);

namespace Tests\Feature\Supplier;

use App\Modules\Supplier\Database\Seeders\SupplierSeeder;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
