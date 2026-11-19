<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\DTO\StockIssueData;
use App\Modules\Inventory\Http\Requests\StockDocumentFilterRequest;
use App\Modules\Inventory\Http\Requests\StoreStockIssueRequest;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Services\IssueStockService;
use App\Modules\Product\Contracts\ProductCatalogContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockIssueController extends Controller
{
    public function __construct(
        private readonly IssueStockService $issues,
        private readonly ProductCatalogContract $products,
        private readonly InventoryQueryContract $inventory,
        private readonly UserDirectoryContract $users,
    ) {}

    public function index(StockDocumentFilterRequest $request): View
    {
        $this->authorize('viewAny', StockIssue::class);

        $filters = $request->validated();

        $issues = StockIssue::query()
            ->withCount('items')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('issued_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('issued_at', '<=', $to))
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return view('inventory::issues.index', [
            'issues' => $issues,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StockIssue::class);

        return view('inventory::issues.create', [
            'products' => $this->products->search('', 500),
            'balances' => $this->inventory->balances(),
        ]);
    }

    public function store(StoreStockIssueRequest $request): RedirectResponse
    {
        $this->authorize('create', StockIssue::class);

        $issue = $this->issues->handle(
            $this->toData($request->validated()),
            (int) $request->user()->id,
        );

        return redirect()
            ->route('inventory.issues.show', $issue)
            ->with('status', 'Pengeluaran stok berhasil disimpan.');
    }

    public function show(StockIssue $issue): View
    {
        $this->authorize('view', $issue);

        $issue->load('items');

        return view('inventory::issues.show', [
            'issue' => $issue,
            'productsById' => $this->products->findManyIncludingDeactivated(
                $issue->items->pluck('product_id')->all(),
            ),
            'actor' => $this->users->find($issue->created_by),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function toData(array $validated): StockIssueData
    {
        return new StockIssueData(
            issuedAt: (string) $validated['issued_at'],
            destination: $validated['destination'] ?? null,
            note: $validated['note'] ?? null,
            items: array_map(static fn (array $item): array => [
                'product_id' => (int) $item['product_id'],
                'qty' => (float) $item['qty'],
            ], $validated['items']),
        );
    }
}
