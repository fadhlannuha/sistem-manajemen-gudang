<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use App\Modules\Product\Models\Product;

final class ProductCatalogService implements ProductCatalogContract
{
    public function find(int $productId): ?ProductSnapshot
    {
        $product = Product::query()
            ->with('unit:id,name,code')
            ->find($productId);

        return $product === null ? null : $this->toSnapshot($product);
    }

    public function findIncludingDeactivated(int $productId): ?ProductSnapshot
    {
        $product = Product::query()
            ->withTrashed()
            ->with('unit:id,name,code')
            ->find($productId);

        return $product === null ? null : $this->toSnapshot($product);
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot>
     */
    public function findMany(array $productIds): array
    {
        return $this->findManyQuery($productIds, withTrashed: false);
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot>
     */
    public function findManyIncludingDeactivated(array $productIds): array
    {
        return $this->findManyQuery($productIds, withTrashed: true);
    }

    public function exists(int $productId): bool
    {
        return Product::query()->whereKey($productId)->exists();
    }

    /**
     * @return array<int, ProductSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array
    {
        // Inventory memakai search('', 1000) untuk menghitung seluruh katalog
        // saat menentukan stok rendah, jadi batasnya harus cukup besar.
        $limit = max(1, min($limit, 1000));
        $term = trim($term);

        $query = Product::query()->with('unit:id,name,code');

        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        return $query
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product): ProductSnapshot => $this->toSnapshot($product))
            ->all();
    }

    public function count(): int
    {
        return Product::query()->count();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot>
     */
    private function findManyQuery(array $productIds, bool $withTrashed): array
    {
        if ($productIds === []) {
            return [];
        }

        $query = Product::query()->with('unit:id,name,code');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query
            ->whereIn('id', $productIds)
            ->get()
            ->mapWithKeys(fn (Product $product): array => [$product->id => $this->toSnapshot($product)])
            ->all();
    }

    private function toSnapshot(Product $product): ProductSnapshot
    {
        return new ProductSnapshot(
            id: $product->id,
            sku: $product->sku,
            name: $product->name,
            unitId: $product->unit_id,
            unitName: $product->unit->name,
            unitCode: $product->unit->code,
            minStock: (float) $product->min_stock,
        );
    }
}
