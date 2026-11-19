<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Services;

use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\DTO\LowStockView;
use App\Modules\Inventory\DTO\StockMovementView;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use App\Modules\Reporting\Models\ActivityLog;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use Illuminate\Support\Collection;

final class DashboardService
{
    public function __construct(
        private readonly ProductCatalogContract $products,
        private readonly SupplierDirectoryContract $suppliers,
        private readonly InventoryQueryContract $inventory,
    ) {}

    /**
     * @return array{
     *     productCount: int,
     *     supplierCount: int,
     *     lowStockCount: int,
     *     lowStock: array<int, array{product: ProductSnapshot|null, qty: float, minStock: float}>,
     *     recentMovements: array<int, array{movement: StockMovementView, product: ProductSnapshot|null}>,
     *     recentActivity: Collection<int, ActivityLog>
     * }
     */
    public function summary(): array
    {
        $lowStock = $this->inventory->lowStock(10);
        $lowStockProducts = $this->products->findMany(array_map(
            static fn (LowStockView $view): int => $view->productId,
            $lowStock,
        ));

        $movements = $this->inventory->recentMovements(5);
        $movementProducts = $movements === []
            ? []
            : $this->products->findManyIncludingDeactivated(array_values(array_unique(array_map(
                static fn (StockMovementView $view): int => $view->productId,
                $movements,
            ))));

        return [
            'productCount' => $this->products->count(),
            'supplierCount' => $this->suppliers->count(),
            'lowStockCount' => $this->inventory->countLowStock(),
            'lowStock' => array_map(
                static fn (LowStockView $view): array => [
                    'product' => $lowStockProducts[$view->productId] ?? null,
                    'qty' => $view->qty,
                    'minStock' => $view->minStock,
                ],
                $lowStock,
            ),
            'recentMovements' => array_map(
                static fn (StockMovementView $view): array => [
                    'movement' => $view,
                    'product' => $movementProducts[$view->productId] ?? null,
                ],
                $movements,
            ),
            'recentActivity' => ActivityLog::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ];
    }
}
