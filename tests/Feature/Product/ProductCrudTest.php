<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Modules\Auth\Models\User;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeactivated;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Unit;
use App\Modules\Product\Services\ProductCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();
        $categories = Category::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'sku' => 'prd-1001',
            'name' => 'Kertas HVS A4',
            'description' => 'Kertas untuk kebutuhan cetak.',
            'unit_id' => $unit->id,
            'min_stock' => 12.5,
            'categories' => $categories->pluck('id')->all(),
        ]);

        $product = Product::query()->where('sku', 'PRD-1001')->firstOrFail();

        $response->assertRedirect(route('products.show', $product));
        $this->assertSame('PRD-1001', $product->sku);
        $this->assertSame('Kertas HVS A4', $product->name);
        $this->assertSame($unit->id, $product->unit_id);
        $this->assertSame(12.5, (float) $product->min_stock);
        $this->assertEqualsCanonicalizing(
            $categories->pluck('id')->all(),
            $product->categories()->pluck('categories.id')->all(),
        );

        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $categories[0]->id,
        ]);
    }

    public function test_sku_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = Product::factory()->create(['sku' => 'PRD-1001']);

        $response = $this->actingAs($admin)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'sku' => 'prd-1001',
                'name' => 'Produk Duplikat',
                'unit_id' => $existing->unit_id,
                'min_stock' => 0,
            ]);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors('sku');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_unit_is_required_and_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $payload = [
            'sku' => 'PRD-1002',
            'name' => 'Produk Tanpa Satuan',
            'min_stock' => 0,
        ];

        $this->actingAs($admin)
            ->post(route('products.store'), $payload)
            ->assertSessionHasErrors('unit_id');

        $this->actingAs($admin)
            ->post(route('products.store'), [...$payload, 'unit_id' => 999999])
            ->assertSessionHasErrors('unit_id');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_min_stock_cannot_be_negative(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'sku' => 'PRD-1003',
                'name' => 'Produk Minus',
                'unit_id' => $unit->id,
                'min_stock' => -1,
            ])
            ->assertSessionHasErrors('min_stock');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->admin()->create();
        $unitA = Unit::factory()->create();
        $unitB = Unit::factory()->create();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $product = Product::factory()->create([
            'sku' => 'PRD-2000',
            'name' => 'Nama Lama',
            'unit_id' => $unitA->id,
        ]);
        $product->categories()->sync([$categoryA->id]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'sku' => 'prd-2001',
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru.',
            'unit_id' => $unitB->id,
            'min_stock' => 7,
            'categories' => [$categoryB->id],
        ]);

        $response->assertRedirect(route('products.show', $product));

        $product->refresh();

        $this->assertSame('PRD-2001', $product->sku);
        $this->assertSame('Nama Baru', $product->name);
        $this->assertSame($unitB->id, $product->unit_id);
        $this->assertSame([$categoryB->id], $product->categories()->pluck('categories.id')->all());
        $this->assertDatabaseMissing('category_product', [
            'product_id' => $product->id,
            'category_id' => $categoryA->id,
        ]);
    }

    public function test_admin_can_soft_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'sku' => 'PRD-3000',
            'name' => 'Barang Untuk Dinonaktifkan',
        ]);

        $response = $this->actingAs($admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $this->actingAs($admin)
            ->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Barang Untuk Dinonaktifkan');
    }

    public function test_soft_deleted_product_is_not_found_by_contract(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $contract = $this->app->make(ProductCatalogContract::class);

        $this->assertInstanceOf(ProductCatalogService::class, $contract);
        $this->assertNull($contract->find($product->id));
        $this->assertFalse($contract->exists($product->id));
        $this->assertArrayNotHasKey($product->id, $contract->findMany([$product->id]));
    }

    public function test_contract_can_return_deactivated_product_for_history(): void
    {
        $product = Product::factory()->create([
            'sku' => 'PRD-3001',
            'name' => 'Barang Historis',
        ]);
        $product->delete();

        $contract = $this->app->make(ProductCatalogContract::class);

        $snapshot = $contract->findIncludingDeactivated($product->id);

        $this->assertNotNull($snapshot);
        $this->assertSame('PRD-3001', $snapshot->sku);
        $this->assertSame('Barang Historis', $snapshot->name);
        $this->assertSame($product->unit->name, $snapshot->unitName);
        $this->assertArrayHasKey($product->id, $contract->findManyIncludingDeactivated([$product->id]));
    }

    public function test_contract_can_search_and_count_products(): void
    {
        $product = Product::factory()->create([
            'sku' => 'PRD-4000',
            'name' => 'Kertas Folio',
        ]);

        Product::factory()->create(['name' => 'Pulpen Biru']);

        $contract = $this->app->make(ProductCatalogContract::class);

        $this->assertSame(2, $contract->count());

        $bySku = $contract->search('PRD-4000');
        $byName = $contract->search('kertas');

        $this->assertCount(1, $bySku);
        $this->assertSame($product->id, $bySku[0]->id);
        $this->assertCount(1, $byName);
        $this->assertSame($product->id, $byName[0]->id);
        $this->assertSame([], $contract->search('tidak-ada-produk-ini'));
    }

    public function test_staff_cannot_create_product(): void
    {
        $staff = User::factory()->staff()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($staff)
            ->get(route('products.create'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('products.store'), [
                'sku' => 'PRD-5000',
                'name' => 'Produk Staff',
                'unit_id' => $unit->id,
                'min_stock' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_staff_can_read_products_but_cannot_modify_them(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create();

        $this->actingAs($staff)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertDontSee('Tambah Barang');

        $this->actingAs($staff)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->sku);

        $this->actingAs($staff)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($staff)->put(route('products.update', $product), [
            'sku' => $product->sku,
            'name' => 'Diubah Staff',
            'unit_id' => $product->unit_id,
            'min_stock' => 0,
        ])->assertForbidden();
        $this->actingAs($staff)->delete(route('products.destroy', $product))->assertForbidden();

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->get(route('units.index'))->assertRedirect(route('login'));
    }

    public function test_product_events_are_dispatched(): void
    {
        Event::fake([ProductCreated::class, ProductUpdated::class, ProductDeactivated::class]);

        $admin = User::factory()->admin()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)->post(route('products.store'), [
            'sku' => 'PRD-6000',
            'name' => 'Produk Event',
            'unit_id' => $unit->id,
            'min_stock' => 3,
        ]);

        Event::assertDispatched(
            ProductCreated::class,
            fn (ProductCreated $event): bool => $event->sku === 'PRD-6000'
                && $event->name === 'Produk Event'
                && $event->unitId === $unit->id
                && $event->minStock === 3.0,
        );

        $product = Product::query()->where('sku', 'PRD-6000')->firstOrFail();

        $this->actingAs($admin)->put(route('products.update', $product), [
            'sku' => 'PRD-6000',
            'name' => 'Produk Event Diperbarui',
            'unit_id' => $unit->id,
            'min_stock' => 4,
        ]);

        Event::assertDispatched(
            ProductUpdated::class,
            fn (ProductUpdated $event): bool => $event->productId === $product->id
                && $event->name === 'Produk Event Diperbarui',
        );

        $this->actingAs($admin)->delete(route('products.destroy', $product));

        Event::assertDispatched(
            ProductDeactivated::class,
            fn (ProductDeactivated $event): bool => $event->productId === $product->id
                && $event->sku === 'PRD-6000',
        );
    }

    public function test_contract_search_can_scan_the_whole_catalog_for_low_stock(): void
    {
        $unit = Unit::factory()->create();
        Product::factory()->count(101)->create(['unit_id' => $unit->id]);

        $contract = $this->app->make(ProductCatalogContract::class);

        // InventoryQueryService memakai search('', 1000) untuk lowStock().
        $this->assertCount(101, $contract->search('', 1000));
    }
}
