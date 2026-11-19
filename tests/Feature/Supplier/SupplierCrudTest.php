<?php

declare(strict_types=1);

namespace Tests\Feature\Supplier;

use App\Modules\Auth\Models\User;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\Events\SupplierCreated;
use App\Modules\Supplier\Events\SupplierDeactivated;
use App\Modules\Supplier\Events\SupplierUpdated;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Services\SupplierCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_supplier(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create(['name' => 'Net 30', 'days' => 30]);

        $response = $this->actingAs($admin)->post(route('suppliers.store'), [
            'code' => 'sup-100',
            'name' => 'PT Sumber Elektronik',
            'contact_name' => 'Budi Santoso',
            'phone' => '+62 21 5550101',
            'email' => 'sales@sumber.test',
            'address' => 'Jl. Industri No. 1, Jakarta',
            'payment_term_id' => $paymentTerm->id,
        ]);

        $supplier = Supplier::query()->where('code', 'SUP-100')->firstOrFail();

        $response->assertRedirect(route('suppliers.show', $supplier));
        $this->assertSame('SUP-100', $supplier->code);
        $this->assertSame('PT Sumber Elektronik', $supplier->name);
        $this->assertSame('Budi Santoso', $supplier->contact_name);
        $this->assertSame($paymentTerm->id, $supplier->payment_term_id);
        $this->assertSame('Net 30', $supplier->paymentTerm->name);
    }

    public function test_supplier_code_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = Supplier::factory()->create(['code' => 'SUP-100']);

        $response = $this->actingAs($admin)
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), [
                'code' => 'sup-100',
                'name' => 'Pemasok Duplikat',
                'payment_term_id' => $existing->payment_term_id,
            ]);

        $response->assertRedirect(route('suppliers.create'));
        $response->assertSessionHasErrors('code');
        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_payment_term_is_required_and_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = [
            'code' => 'SUP-101',
            'name' => 'Pemasok Tanpa Termin',
        ];

        $this->actingAs($admin)
            ->post(route('suppliers.store'), $payload)
            ->assertSessionHasErrors('payment_term_id');

        $this->actingAs($admin)
            ->post(route('suppliers.store'), [...$payload, 'payment_term_id' => 999999])
            ->assertSessionHasErrors('payment_term_id');

        $this->assertDatabaseCount('suppliers', 0);
    }

    public function test_supplier_contact_fields_are_validated(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create();

        $payload = [
            'code' => 'SUP-102',
            'name' => 'Pemasok Kontak',
            'payment_term_id' => $paymentTerm->id,
        ];

        $this->actingAs($admin)
            ->post(route('suppliers.store'), [...$payload, 'phone' => 'telepon tidak valid'])
            ->assertSessionHasErrors('phone');

        $this->actingAs($admin)
            ->post(route('suppliers.store'), [...$payload, 'email' => 'bukan-email'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('suppliers', 0);
    }

    public function test_admin_can_update_supplier(): void
    {
        $admin = User::factory()->admin()->create();
        $termA = PaymentTerm::factory()->create();
        $termB = PaymentTerm::factory()->create();
        $supplier = Supplier::factory()->create([
            'code' => 'SUP-200',
            'name' => 'Nama Lama',
            'payment_term_id' => $termA->id,
        ]);

        $response = $this->actingAs($admin)->put(route('suppliers.update', $supplier), [
            'code' => 'sup-201',
            'name' => 'Nama Baru',
            'contact_name' => 'Kontak Baru',
            'phone' => '+62 21 5550202',
            'email' => 'baru@pemasok.test',
            'address' => 'Alamat baru.',
            'payment_term_id' => $termB->id,
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));

        $supplier->refresh();

        $this->assertSame('SUP-201', $supplier->code);
        $this->assertSame('Nama Baru', $supplier->name);
        $this->assertSame('Kontak Baru', $supplier->contact_name);
        $this->assertSame($termB->id, $supplier->payment_term_id);
    }

    public function test_admin_can_soft_delete_supplier(): void
    {
        $admin = User::factory()->admin()->create();
        $supplier = Supplier::factory()->create([
            'code' => 'SUP-300',
            'name' => 'Pemasok Untuk Dinonaktifkan',
        ]);

        $response = $this->actingAs($admin)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

        $this->actingAs($admin)
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertDontSee('Pemasok Untuk Dinonaktifkan');

        $this->actingAs($admin)
            ->get(route('suppliers.show', $supplier))
            ->assertNotFound();
    }

    public function test_contract_hides_soft_deleted_supplier_but_keeps_history(): void
    {
        $supplier = Supplier::factory()->create([
            'code' => 'SUP-301',
            'name' => 'Pemasok Historis',
        ]);
        $supplier->delete();

        $contract = $this->app->make(SupplierDirectoryContract::class);

        $this->assertInstanceOf(SupplierCatalogService::class, $contract);
        $this->assertNull($contract->find($supplier->id));
        $this->assertFalse($contract->exists($supplier->id));
        $this->assertArrayNotHasKey($supplier->id, $contract->findMany([$supplier->id]));

        $snapshot = $contract->findIncludingDeactivated($supplier->id);

        $this->assertNotNull($snapshot);
        $this->assertSame('SUP-301', $snapshot->code);
        $this->assertSame('Pemasok Historis', $snapshot->name);
        $this->assertSame($supplier->paymentTerm->name, $snapshot->paymentTermName);
    }

    public function test_contract_can_search_and_count_suppliers(): void
    {
        $supplier = Supplier::factory()->create([
            'code' => 'SUP-400',
            'name' => 'PT Kertas Prima',
        ]);

        Supplier::factory()->create(['name' => 'CV Kemas Jaya']);

        $contract = $this->app->make(SupplierDirectoryContract::class);

        $this->assertSame(2, $contract->count());

        $byCode = $contract->search('SUP-400');
        $byName = $contract->search('kertas');

        $this->assertCount(1, $byCode);
        $this->assertSame($supplier->id, $byCode[0]->id);
        $this->assertCount(1, $byName);
        $this->assertSame($supplier->id, $byName[0]->id);
        $this->assertSame([], $contract->search('tidak-ada-pemasok-ini'));
    }

    public function test_staff_can_read_suppliers_but_cannot_modify_them(): void
    {
        $staff = User::factory()->staff()->create();
        $supplier = Supplier::factory()->create();
        $paymentTerm = PaymentTerm::factory()->create();

        $this->actingAs($staff)
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee($supplier->name)
            ->assertDontSee('Tambah Pemasok');

        $this->actingAs($staff)
            ->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->code);

        $this->actingAs($staff)->get(route('suppliers.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('suppliers.store'), [
            'code' => 'SUP-500',
            'name' => 'Pemasok Staff',
            'payment_term_id' => $paymentTerm->id,
        ])->assertForbidden();
        $this->actingAs($staff)->get(route('suppliers.edit', $supplier))->assertForbidden();
        $this->actingAs($staff)->put(route('suppliers.update', $supplier), [
            'code' => $supplier->code,
            'name' => 'Diubah Staff',
            'payment_term_id' => $supplier->payment_term_id,
        ])->assertForbidden();
        $this->actingAs($staff)->delete(route('suppliers.destroy', $supplier))->assertForbidden();

        $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('suppliers.index'))->assertRedirect(route('login'));
        $this->get(route('payment-terms.index'))->assertRedirect(route('login'));
    }

    public function test_supplier_events_are_dispatched(): void
    {
        Event::fake([SupplierCreated::class, SupplierUpdated::class, SupplierDeactivated::class]);

        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create();

        $this->actingAs($admin)->post(route('suppliers.store'), [
            'code' => 'SUP-600',
            'name' => 'Pemasok Event',
            'payment_term_id' => $paymentTerm->id,
        ]);

        Event::assertDispatched(
            SupplierCreated::class,
            fn (SupplierCreated $event): bool => $event->code === 'SUP-600'
                && $event->name === 'Pemasok Event'
                && $event->paymentTermId === $paymentTerm->id,
        );

        $supplier = Supplier::query()->where('code', 'SUP-600')->firstOrFail();

        $this->actingAs($admin)->put(route('suppliers.update', $supplier), [
            'code' => 'SUP-600',
            'name' => 'Pemasok Event Diperbarui',
            'payment_term_id' => $paymentTerm->id,
        ]);

        Event::assertDispatched(
            SupplierUpdated::class,
            fn (SupplierUpdated $event): bool => $event->supplierId === $supplier->id
                && $event->name === 'Pemasok Event Diperbarui',
        );

        $this->actingAs($admin)->delete(route('suppliers.destroy', $supplier));

        Event::assertDispatched(
            SupplierDeactivated::class,
            fn (SupplierDeactivated $event): bool => $event->supplierId === $supplier->id
                && $event->code === 'SUP-600',
        );
    }
}
