<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use App\Modules\Product\DTO\ProductSnapshot;

interface ProductCatalogContract
{
    public function find(int $productId): ?ProductSnapshot;

    /**
     * Untuk kebutuhan historis (kartu stok, dokumen lama): tetap
     * mengembalikan produk yang sudah soft-deleted.
     */
    public function findIncludingDeactivated(int $productId): ?ProductSnapshot;

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot> keyed by product id
     */
    public function findMany(array $productIds): array;

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, ProductSnapshot> keyed by product id; termasuk yang soft-deleted
     */
    public function findManyIncludingDeactivated(array $productIds): array;

    public function exists(int $productId): bool;

    /**
     * @return array<int, ProductSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array;

    public function count(): int;
}
