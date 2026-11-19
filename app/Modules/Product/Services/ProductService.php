<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeactivated;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProductService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $categoryIds = $this->categoryIds($data);

        $product = DB::transaction(function () use ($data, $categoryIds): Product {
            $attributes = Arr::except($data, ['categories']);
            $attributes['sku'] = Str::upper(trim((string) $attributes['sku']));

            $product = Product::query()->create($attributes);
            $product->categories()->sync($categoryIds);

            return $product;
        });

        ProductCreated::dispatch(
            productId: $product->id,
            sku: $product->sku,
            name: $product->name,
            unitId: $product->unit_id,
            minStock: (float) $product->min_stock,
        );

        return $product->load('unit', 'categories');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $categoryIds = $this->categoryIds($data);

        DB::transaction(function () use ($product, $data, $categoryIds): void {
            $attributes = Arr::except($data, ['categories']);
            $attributes['sku'] = Str::upper(trim((string) $attributes['sku']));

            $product->update($attributes);
            $product->categories()->sync($categoryIds);
        });

        ProductUpdated::dispatch(
            productId: $product->id,
            sku: $product->sku,
            name: $product->name,
            unitId: $product->unit_id,
            minStock: (float) $product->min_stock,
        );

        return $product->load('unit', 'categories');
    }

    public function deactivate(Product $product): void
    {
        $product->delete();

        ProductDeactivated::dispatch(
            productId: $product->id,
            sku: $product->sku,
            name: $product->name,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, int>
     */
    private function categoryIds(array $data): array
    {
        return array_map('intval', $data['categories'] ?? []);
    }
}
