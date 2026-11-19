<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Contracts;

use App\Modules\Supplier\DTO\SupplierSnapshot;

interface SupplierDirectoryContract
{
    public function find(int $supplierId): ?SupplierSnapshot;

    /**
     * Untuk menampilkan dokumen lama milik pemasok yang sudah soft-deleted.
     */
    public function findIncludingDeactivated(int $supplierId): ?SupplierSnapshot;

    /**
     * @param  array<int, int>  $supplierIds
     * @return array<int, SupplierSnapshot>
     */
    public function findMany(array $supplierIds): array;

    public function exists(int $supplierId): bool;

    /**
     * @return array<int, SupplierSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array;

    public function count(): int;
}
