<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Modules\Auth\Models\User;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_unit_with_uppercase_code(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('units.store'), [
            'name' => 'Lusin',
            'code' => 'lsn',
        ]);

        $response->assertRedirect(route('units.index'));

        $this->assertDatabaseHas('units', [
            'name' => 'Lusin',
            'code' => 'LSN',
        ]);
    }

    public function test_unit_name_and_code_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Unit::factory()->create(['name' => 'Piece', 'code' => 'PCS']);

        $this->actingAs($admin)
            ->from(route('units.create'))
            ->post(route('units.store'), ['name' => 'piece', 'code' => 'PCS2'])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->from(route('units.create'))
            ->post(route('units.store'), ['name' => 'Kotak', 'code' => 'pcs'])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('units', 1);
    }

    public function test_admin_can_update_unit(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create(['name' => 'Piece', 'code' => 'PCS']);

        $response = $this->actingAs($admin)->put(route('units.update', $unit), [
            'name' => 'Box',
            'code' => 'box',
        ]);

        $response->assertRedirect(route('units.index'));

        $unit->refresh();

        $this->assertSame('Box', $unit->name);
        $this->assertSame('BOX', $unit->code);
    }

    public function test_admin_can_delete_unused_unit(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->delete(route('units.destroy', $unit));

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_unit_cannot_be_deleted_when_used(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();
        Product::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($admin)
            ->from(route('units.index'))
            ->delete(route('units.destroy', $unit));

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }

    public function test_unit_cannot_be_deleted_when_used_by_soft_deleted_product(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();
        $product = Product::factory()->create(['unit_id' => $unit->id]);
        $product->delete();

        $response = $this->actingAs($admin)
            ->from(route('units.index'))
            ->delete(route('units.destroy', $unit));

        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('units', ['id' => $unit->id]);

        $this->actingAs($admin)
            ->get(route('units.index'))
            ->assertOk()
            ->assertSee('Produk (aktif/total)')
            ->assertSee('0 / 1');
    }

    public function test_staff_cannot_modify_units(): void
    {
        $staff = User::factory()->staff()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($staff)->get(route('units.index'))->assertOk();
        $this->actingAs($staff)->get(route('units.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('units.store'), ['name' => 'Baru', 'code' => 'NEW'])->assertForbidden();
        $this->actingAs($staff)->get(route('units.edit', $unit))->assertForbidden();
        $this->actingAs($staff)->put(route('units.update', $unit), ['name' => 'Diubah', 'code' => 'UBAH'])->assertForbidden();
        $this->actingAs($staff)->delete(route('units.destroy', $unit))->assertForbidden();

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => $unit->name]);
    }
}
