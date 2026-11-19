<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Services;

use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\DTO\SupplierSnapshot;
use App\Modules\Supplier\Models\Supplier;

final class SupplierCatalogService implements SupplierDirectoryContract
{
    public function find(int $supplierId): ?SupplierSnapshot
    {
        $supplier = Supplier::query()
            ->with('paymentTerm:id,name,days')
            ->find($supplierId);

        return $supplier === null ? null : $this->toSnapshot($supplier);
    }

    public function findIncludingDeactivated(int $supplierId): ?SupplierSnapshot
    {
        $supplier = Supplier::query()
            ->withTrashed()
            ->with('paymentTerm:id,name,days')
            ->find($supplierId);

        return $supplier === null ? null : $this->toSnapshot($supplier);
    }

    /**
     * @param  array<int, int>  $supplierIds
     * @return array<int, SupplierSnapshot>
     */
    public function findMany(array $supplierIds): array
    {
        if ($supplierIds === []) {
            return [];
        }

        return Supplier::query()
            ->with('paymentTerm:id,name,days')
            ->whereIn('id', $supplierIds)
            ->get()
            ->mapWithKeys(fn (Supplier $supplier): array => [$supplier->id => $this->toSnapshot($supplier)])
            ->all();
    }

    public function exists(int $supplierId): bool
    {
        return Supplier::query()->whereKey($supplierId)->exists();
    }

    /**
     * @return array<int, SupplierSnapshot>
     */
    public function search(string $term = '', int $limit = 20): array
    {
        $limit = max(1, min($limit, 100));
        $term = trim($term);

        $query = Supplier::query()->with('paymentTerm:id,name,days');

        if ($term !== '') {
            $query->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        return $query
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Supplier $supplier): SupplierSnapshot => $this->toSnapshot($supplier))
            ->all();
    }

    public function count(): int
    {
        return Supplier::query()->count();
    }

    private function toSnapshot(Supplier $supplier): SupplierSnapshot
    {
        return new SupplierSnapshot(
            id: $supplier->id,
            code: $supplier->code,
            name: $supplier->name,
            paymentTermId: $supplier->payment_term_id,
            paymentTermName: $supplier->paymentTerm->name,
            paymentTermDays: (int) $supplier->paymentTerm->days,
        );
    }
}
