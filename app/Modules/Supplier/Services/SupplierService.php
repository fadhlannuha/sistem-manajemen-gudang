<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Services;

use App\Modules\Supplier\Events\SupplierCreated;
use App\Modules\Supplier\Events\SupplierDeactivated;
use App\Modules\Supplier\Events\SupplierUpdated;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SupplierService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Supplier
    {
        $supplier = DB::transaction(function () use ($data): Supplier {
            $data['code'] = Str::upper(trim((string) $data['code']));

            return Supplier::query()->create($data);
        });

        SupplierCreated::dispatch(
            supplierId: $supplier->id,
            code: $supplier->code,
            name: $supplier->name,
            paymentTermId: $supplier->payment_term_id,
        );

        return $supplier->load('paymentTerm');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        DB::transaction(function () use ($supplier, $data): void {
            $data['code'] = Str::upper(trim((string) $data['code']));

            $supplier->update($data);
        });

        SupplierUpdated::dispatch(
            supplierId: $supplier->id,
            code: $supplier->code,
            name: $supplier->name,
            paymentTermId: $supplier->payment_term_id,
        );

        return $supplier->load('paymentTerm');
    }

    public function deactivate(Supplier $supplier): void
    {
        $supplier->delete();

        SupplierDeactivated::dispatch(
            supplierId: $supplier->id,
            code: $supplier->code,
            name: $supplier->name,
        );
    }
}
