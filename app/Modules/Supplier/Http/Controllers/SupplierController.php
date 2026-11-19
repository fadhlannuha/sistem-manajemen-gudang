<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Controllers;

use App\Modules\Supplier\Http\Requests\StoreSupplierRequest;
use App\Modules\Supplier\Http\Requests\SupplierFilterRequest;
use App\Modules\Supplier\Http\Requests\UpdateSupplierRequest;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers) {}

    public function index(SupplierFilterRequest $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $filters = $request->validated();

        $suppliers = Supplier::query()
            ->with('paymentTerm:id,name,days')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['payment_term_id'] ?? null, function ($query, string|int $paymentTermId): void {
                $query->where('payment_term_id', $paymentTermId);
            })
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return view('supplier::suppliers.index', [
            'suppliers' => $suppliers,
            'paymentTerms' => PaymentTerm::query()->orderBy('days')->orderBy('name')->get(['id', 'name', 'days']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('supplier::suppliers.create', [
            'paymentTerms' => PaymentTerm::query()->orderBy('days')->orderBy('name')->get(['id', 'name', 'days']),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $supplier = $this->suppliers->create($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Pemasok berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        $supplier->load('paymentTerm');

        return view('supplier::suppliers.show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('supplier::suppliers.edit', [
            'supplier' => $supplier,
            'paymentTerms' => PaymentTerm::query()->orderBy('days')->orderBy('name')->get(['id', 'name', 'days']),
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $this->suppliers->update($supplier, $request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $this->suppliers->deactivate($supplier);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Pemasok berhasil dinonaktifkan.');
    }
}
