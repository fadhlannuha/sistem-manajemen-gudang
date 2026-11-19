<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Inventory\DTO\StockReceiptData;
use App\Modules\Inventory\Http\Requests\StockDocumentFilterRequest;
use App\Modules\Inventory\Http\Requests\StoreStockReceiptRequest;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Inventory\Services\ReceiveStockService;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\DTO\SupplierSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiveStockService $receipts,
        private readonly SupplierDirectoryContract $suppliers,
        private readonly ProductCatalogContract $products,
        private readonly UserDirectoryContract $users,
    ) {}

    public function index(StockDocumentFilterRequest $request): View
    {
        $this->authorize('viewAny', StockReceipt::class);

        $filters = $request->validated();

        $receipts = StockReceipt::query()
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $supplierIds = array_map(
                    static fn (SupplierSnapshot $supplier): int => $supplier->id,
                    $this->suppliers->search($search, 100),
                );

                $query->where(function ($query) use ($search, $supplierIds): void {
                    $query->where('number', 'like', "%{$search}%");

                    if ($supplierIds !== []) {
                        $query->orWhereIn('supplier_id', $supplierIds);
                    }
                });
            })
            ->when($filters['supplier_id'] ?? null, fn ($query, string|int $supplierId) => $query->where('supplier_id', $supplierId))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('received_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('received_at', '<=', $to))
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return view('inventory::receipts.index', [
            'receipts' => $receipts,
            'suppliersById' => $this->supplierSnapshots($receipts->pluck('supplier_id')->all()),
            'supplierOptions' => $this->suppliers->search('', 100),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StockReceipt::class);

        return view('inventory::receipts.create', [
            'suppliers' => $this->suppliers->search('', 100),
            'products' => $this->products->search('', 500),
        ]);
    }

    public function store(StoreStockReceiptRequest $request): RedirectResponse
    {
        $this->authorize('create', StockReceipt::class);

        $receipt = $this->receipts->handle(
            $this->toData($request->validated()),
            (int) $request->user()->id,
        );

        return redirect()
            ->route('inventory.receipts.show', $receipt)
            ->with('status', 'Penerimaan stok berhasil disimpan.');
    }

    public function show(StockReceipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load('items');

        return view('inventory::receipts.show', [
            'receipt' => $receipt,
            'supplier' => $this->suppliers->findIncludingDeactivated($receipt->supplier_id),
            'productsById' => $this->products->findManyIncludingDeactivated(
                $receipt->items->pluck('product_id')->all(),
            ),
            'actor' => $this->users->find($receipt->created_by),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function toData(array $validated): StockReceiptData
    {
        return new StockReceiptData(
            supplierId: (int) $validated['supplier_id'],
            receivedAt: (string) $validated['received_at'],
            note: $validated['note'] ?? null,
            items: array_map(static fn (array $item): array => [
                'product_id' => (int) $item['product_id'],
                'qty' => (float) $item['qty'],
                'unit_cost' => (float) $item['unit_cost'],
            ], $validated['items']),
        );
    }

    /**
     * @param  array<int, int>  $supplierIds
     * @return array<int, SupplierSnapshot>
     */
    private function supplierSnapshots(array $supplierIds): array
    {
        $snapshots = [];

        foreach (array_unique(array_map('intval', $supplierIds)) as $supplierId) {
            $snapshot = $this->suppliers->findIncludingDeactivated($supplierId);

            if ($snapshot !== null) {
                $snapshots[$supplierId] = $snapshot;
            }
        }

        return $snapshots;
    }
}
