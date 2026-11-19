<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Modules\Auth\Models\User;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_with_generated_slug(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Alat Tulis',
            'description' => 'Kebutuhan administrasi.',
        ]);

        $response->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Alat Tulis',
            'slug' => 'alat-tulis',
            'description' => 'Kebutuhan administrasi.',
        ]);
    }

    public function test_category_slug_is_made_unique(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Alat Tulis']);
        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Alat-Tulis']);

        $this->assertDatabaseHas('categories', ['slug' => 'alat-tulis']);
        $this->assertDatabaseHas('categories', ['slug' => 'alat-tulis-2']);
    }

    public function test_category_name_must_be_unique_case_insensitively(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Alat Tulis']);

        $response = $this->actingAs($admin)
            ->from(route('categories.create'))
            ->post(route('categories.store'), ['name' => 'alat tulis']);

        $response->assertRedirect(route('categories.create'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Alat Tulis', 'slug' => 'alat-tulis']);

        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Alat Tulis Kantor',
            'description' => 'Deskripsi baru.',
        ]);

        $response->assertRedirect(route('categories.index'));

        $category->refresh();

        $this->assertSame('Alat Tulis Kantor', $category->name);
        $this->assertSame('alat-tulis-kantor', $category->slug);
        $this->assertSame('Deskripsi baru.', $category->description);
    }

    public function test_category_update_rejects_duplicate_name(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Elektronik']);
        $category = Category::factory()->create(['name' => 'Kebersihan']);

        $response = $this->actingAs($admin)
            ->from(route('categories.edit', $category))
            ->put(route('categories.update', $category), ['name' => 'elektronik']);

        $response->assertRedirect(route('categories.edit', $category));
        $response->assertSessionHasErrors('name');
        $this->assertSame('Kebersihan', $category->refresh()->name);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_deleted_when_used(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->sync([$category->id]);

        $response = $this->actingAs($admin)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_deleted_when_used_by_soft_deleted_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->sync([$category->id]);
        $product->delete();

        $response = $this->actingAs($admin)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category));

        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_staff_cannot_modify_categories(): void
    {
        $staff = User::factory()->staff()->create();
        $category = Category::factory()->create();

        $this->actingAs($staff)->get(route('categories.index'))->assertOk();
        $this->actingAs($staff)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('categories.store'), ['name' => 'Baru'])->assertForbidden();
        $this->actingAs($staff)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($staff)->put(route('categories.update', $category), ['name' => 'Diubah'])->assertForbidden();
        $this->actingAs($staff)->delete(route('categories.destroy', $category))->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
    }
}
