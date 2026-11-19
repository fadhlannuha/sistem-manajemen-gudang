<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\Http\Requests\StockCardFilterRequest;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Contracts\ProductCatalogContract;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StockCardController extends Controller
{
    public function __construct(
        private readonly ProductCatalogContract $products,
        private readonly InventoryQueryContract $inventory,
    ) {}

    public function index(StockCardFilterRequest $request): View
    {
        $filters = $request->validated();
        $productId = isset($filters['product_id']) ? (int) $filters['product_id'] : null;

        $product = null;
        $movements = null;

        if ($productId !== null) {
            $product = $this->products->findIncludingDeactivated($productId);

            abort_if($product === null, 404);

            $movements = StockMovement::query()
                ->where('product_id', $productId)
                ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('movement_date', '>=', $from))
                ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('movement_date', '<=', $to))
                ->orderBy('movement_date')
                ->orderBy('id')
                ->paginate(50)
                ->withQueryString();
        }

        return view('inventory::stock-card.index', [
            'products' => $this->products->search('', 500),
            'product' => $product,
            'movements' => $movements,
            'referenceNumbers' => $movements === null ? [] : $this->referenceNumbers($movements->getCollection()),
            'balance' => $productId === null ? null : $this->inventory->balanceFor($productId),
            'filters' => $filters,
        ]);
    }

    /**
     * @param  Collection<int, StockMovement>  $movements
     * @return array<int, string>
     */
    private function referenceNumbers(Collection $movements): array
    {
        $receiptIds = $movements
            ->where('reference_type', 'stock_receipt')
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $issueIds = $movements
            ->where('reference_type', 'stock_issue')
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $receiptNumbers = $receiptIds === []
            ? collect()
            : StockReceipt::query()->whereIn('id', $receiptIds)->pluck('number', 'id');

        $issueNumbers = $issueIds === []
            ? collect()
            : StockIssue::query()->whereIn('id', $issueIds)->pluck('number', 'id');

        $numbers = [];

        foreach ($movements as $movement) {
            $number = match ($movement->reference_type) {
                'stock_receipt' => $receiptNumbers[$movement->reference_id] ?? null,
                'stock_issue' => $issueNumbers[$movement->reference_id] ?? null,
                default => null,
            };

            if ($number !== null) {
                $numbers[$movement->id] = (string) $number;
            }
        }

        return $numbers;
    }
}
