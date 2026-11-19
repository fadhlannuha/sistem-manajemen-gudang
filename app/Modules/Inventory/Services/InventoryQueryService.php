<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\DTO\LowStockView;
use App\Modules\Inventory\DTO\StockBalanceView;
use App\Modules\Inventory\DTO\StockMovementView;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Contracts\ProductCatalogContract;
use Illuminate\Support\Collection;

final class InventoryQueryService implements InventoryQueryContract
{
    public function __construct(private readonly ProductCatalogContract $products) {}

    public function balanceFor(int $productId): ?StockBalanceView
    {
        $balance = StockBalance::query()->where('product_id', $productId)->first();

        return $balance === null ? null : $this->toBalanceView($balance);
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, StockBalanceView>
     */
    public function balances(array $productIds = []): array
    {
        return StockBalance::query()
            ->when($productIds !== [], fn ($query) => $query->whereIn('product_id', $productIds))
            ->get()
            ->mapWithKeys(fn (StockBalance $balance): array => [
                $balance->product_id => $this->toBalanceView($balance),
            ])
            ->all();
    }

    /**
     * @return array<int, StockMovementView>
     */
    public function movementsFor(int $productId, ?string $from = null, ?string $to = null): array
    {
        $movements = StockMovement::query()
            ->where('product_id', $productId)
            ->when($from !== null && $from !== '', fn ($query) => $query->whereDate('movement_date', '>=', $from))
            ->when($to !== null && $to !== '', fn ($query) => $query->whereDate('movement_date', '<=', $to))
            ->orderBy('movement_date')
            ->orderBy('id')
            ->get();

        return $this->toMovementViews($movements);
    }

    /**
     * @return array<int, StockMovementView>
     */
    public function recentMovements(int $limit = 5): array
    {
        $limit = max(1, min($limit, 100));

        $movements = StockMovement::query()
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $this->toMovementViews($movements);
    }

    /**
     * @return array<int, LowStockView>
     */
    public function lowStock(int $limit = 20): array
    {
        $limit = max(1, min($limit, 1000));
        $products = $this->products->search('', 1000);
        $balances = StockBalance::query()->get()->keyBy('product_id');

        $lowStock = [];

        foreach ($products as $product) {
            $qty = (float) ($balances[$product->id]->qty ?? 0);

            if ($qty <= $product->minStock) {
                $lowStock[] = new LowStockView($product->id, $qty, $product->minStock);
            }
        }

        usort(
            $lowStock,
            static fn (LowStockView $a, LowStockView $b): int => [$a->qty, $a->productId] <=> [$b->qty, $b->productId],
        );

        return array_slice($lowStock, 0, $limit);
    }

    public function countLowStock(): int
    {
        return count($this->lowStock(1000));
    }

    public function hasMovements(int $productId): bool
    {
        return StockMovement::query()->where('product_id', $productId)->exists();
    }

    private function toBalanceView(StockBalance $balance): StockBalanceView
    {
        return new StockBalanceView(
            productId: $balance->product_id,
            qty: (float) $balance->qty,
            updatedAt: $balance->updated_at?->toIso8601String(),
        );
    }

    /**
     * @param  Collection<int, StockMovement>  $movements
     * @return array<int, StockMovementView>
     */
    private function toMovementViews(Collection $movements): array
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

        return $movements->map(function (StockMovement $movement) use ($receiptNumbers, $issueNumbers): StockMovementView {
            $referenceNumber = match ($movement->reference_type) {
                'stock_receipt' => $receiptNumbers[$movement->reference_id] ?? null,
                'stock_issue' => $issueNumbers[$movement->reference_id] ?? null,
                default => null,
            };

            return new StockMovementView(
                id: $movement->id,
                productId: $movement->product_id,
                type: $movement->movement_type,
                referenceType: $movement->reference_type,
                referenceId: $movement->reference_id,
                referenceNumber: $referenceNumber,
                movementDate: $movement->movement_date->format('Y-m-d'),
                qtyIn: (float) $movement->qty_in,
                qtyOut: (float) $movement->qty_out,
                balanceAfter: (float) $movement->balance_after,
                unitCost: $movement->unit_cost === null ? null : (float) $movement->unit_cost,
                note: $movement->note,
            );
        })->all();
    }
}
