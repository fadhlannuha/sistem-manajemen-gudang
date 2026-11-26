<?php

declare(strict_types=1);

namespace Tests\Feature\Supplier;

use App\Modules\Auth\Models\User;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTermCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_payment_term(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('payment-terms.store'), [
            'name' => 'Net 45',
            'days' => 45,
        ]);

        $response->assertRedirect(route('payment-terms.index'));
        $this->assertDatabaseHas('payment_terms', ['name' => 'Net 45', 'days' => 45]);
    }

    public function test_payment_term_name_must_be_unique_case_insensitively(): void
    {
        $admin = User::factory()->admin()->create();
        PaymentTerm::factory()->create(['name' => 'Net 30', 'days' => 30]);

        $response = $this->actingAs($admin)
            ->from(route('payment-terms.create'))
            ->post(route('payment-terms.store'), ['name' => 'net 30', 'days' => 14]);

        $response->assertRedirect(route('payment-terms.create'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('payment_terms', 1);
    }

    public function test_payment_term_days_must_be_between_zero_and_365(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('payment-terms.store'), ['name' => 'Minus', 'days' => -1])
            ->assertSessionHasErrors('days');

        $this->actingAs($admin)
            ->post(route('payment-terms.store'), ['name' => 'Terlalu Lama', 'days' => 366])
            ->assertSessionHasErrors('days');

        $this->actingAs($admin)
            ->post(route('payment-terms.store'), ['name' => 'Tanpa Hari'])
            ->assertSessionHasErrors('days');

        $this->assertDatabaseCount('payment_terms', 0);
    }

    public function test_admin_can_update_payment_term(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create(['name' => 'Net 14', 'days' => 14]);

        $response = $this->actingAs($admin)->put(route('payment-terms.update', $paymentTerm), [
            'name' => 'Net 21',
            'days' => 21,
        ]);

        $response->assertRedirect(route('payment-terms.index'));

        $paymentTerm->refresh();

        $this->assertSame('Net 21', $paymentTerm->name);
        $this->assertSame(21, $paymentTerm->days);
    }

    public function test_admin_can_delete_unused_payment_term(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create();

        $response = $this->actingAs($admin)->delete(route('payment-terms.destroy', $paymentTerm));

        $response->assertRedirect(route('payment-terms.index'));
        $this->assertDatabaseMissing('payment_terms', ['id' => $paymentTerm->id]);
    }

    public function test_payment_term_cannot_be_deleted_when_used(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create();
        Supplier::factory()->create(['payment_term_id' => $paymentTerm->id]);

        $response = $this->actingAs($admin)
            ->from(route('payment-terms.index'))
            ->delete(route('payment-terms.destroy', $paymentTerm));

        $response->assertRedirect(route('payment-terms.index'));
        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('payment_terms', ['id' => $paymentTerm->id]);
    }

    public function test_payment_term_cannot_be_deleted_when_used_by_soft_deleted_supplier(): void
    {
        $admin = User::factory()->admin()->create();
        $paymentTerm = PaymentTerm::factory()->create();
        $supplier = Supplier::factory()->create(['payment_term_id' => $paymentTerm->id]);
        $supplier->delete();

        $response = $this->actingAs($admin)
            ->from(route('payment-terms.index'))
            ->delete(route('payment-terms.destroy', $paymentTerm));

        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('payment_terms', ['id' => $paymentTerm->id]);

        $this->actingAs($admin)
            ->get(route('payment-terms.index'))
            ->assertOk()
            ->assertSee('Pemasok (aktif/total)')
            ->assertSee('0 / 1');
    }

    public function test_staff_cannot_modify_payment_terms(): void
    {
        $staff = User::factory()->staff()->create();
        $paymentTerm = PaymentTerm::factory()->create();

        $this->actingAs($staff)
            ->get(route('payment-terms.index'))
            ->assertOk()
            ->assertDontSee('Tambah Termin');

        $this->actingAs($staff)->get(route('payment-terms.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('payment-terms.store'), ['name' => 'Baru', 'days' => 1])->assertForbidden();
        $this->actingAs($staff)->get(route('payment-terms.edit', $paymentTerm))->assertForbidden();
        $this->actingAs($staff)->put(route('payment-terms.update', $paymentTerm), ['name' => 'Diubah', 'days' => 2])->assertForbidden();
        $this->actingAs($staff)->delete(route('payment-terms.destroy', $paymentTerm))->assertForbidden();

        $this->assertDatabaseHas('payment_terms', ['id' => $paymentTerm->id, 'name' => $paymentTerm->name]);
    }
}
