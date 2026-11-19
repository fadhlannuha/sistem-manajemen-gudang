<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\StockBalanceFilterRequest;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use Illuminate\View\View;

class StockBalanceController extends Controller
{
    public function __construct(private readonly ProductCatalogContract $products) {}

    public function index(StockBalanceFilterRequest $request): View
    {
        $filters = $request->validated();
        $search = (string) ($filters['search'] ?? '');

        $snapshots = $this->products->search($search, 1000);
        $productIds = array_map(
            static fn (ProductSnapshot $product): int => $product->id,
            $snapshots,
        );

        $query = StockBalance::query()->whereIn('product_id', $productIds);

        if ($request->boolean('low_stock')) {
            $query->whereIn('product_id', $this->lowStockProductIds($snapshots));
        }

        $balances = $query
            ->orderBy('product_id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        return view('inventory::balances.index', [
            'balances' => $balances,
            'productsById' => $this->products->findMany($balances->pluck('product_id')->all()),
            'filters' => $filters,
        ]);
    }

    /**
     * @param  array<int, ProductSnapshot>  $snapshots
     * @return array<int, int>
     */
    private function lowStockProductIds(array $snapshots): array
    {
        $balances = StockBalance::query()->get()->keyBy('product_id');
        $lowStockIds = [];

        foreach ($snapshots as $product) {
            $qty = (float) ($balances[$product->id]->qty ?? 0);

            if ($qty <= $product->minStock) {
                $lowStockIds[] = $product->id;
            }
        }

        return $lowStockIds;
    }
}
