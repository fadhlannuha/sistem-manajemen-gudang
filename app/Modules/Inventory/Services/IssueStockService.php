<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTO\StockIssueData;
use App\Modules\Inventory\Events\LowStockDetected;
use App\Modules\Inventory\Events\StockIssued;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use App\Modules\Product\Exceptions\ProductUnavailableException;
use Illuminate\Support\Facades\DB;

final class IssueStockService
{
    public function __construct(
        private readonly ProductCatalogContract $products,
        private readonly StockNumberGenerator $numbers,
    ) {}

    public function handle(StockIssueData $data, int $actorId): StockIssue
    {
        $snapshots = $this->productSnapshots($data);

        [$issue, $lowStock] = DB::transaction(function () use ($data, $actorId, $snapshots): array {
            $issue = StockIssue::query()->create([
                'number' => null,
                'issued_at' => $data->issuedAt,
                'destination' => $data->destination,
                'created_by' => $actorId,
                'note' => $data->note,
            ]);

            $lowStock = [];

            foreach ($this->sortedItems($data->items) as $item) {
                $productId = (int) $item['product_id'];
                $qty = (float) $item['qty'];

                $balance = StockBalance::query()
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $available = (float) $balance->qty;

                if ($available < $qty) {
                    throw new InsufficientStockException(
                        productId: $productId,
                        productName: $snapshots[$productId]->name,
                        requested: $qty,
                        available: $available,
                    );
                }

                $issue->items()->create([
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);

                $qtyAfter = round($available - $qty, 3);
                $balance->update(['qty' => $qtyAfter]);

                StockMovement::query()->create([
                    'product_id' => $productId,
                    'movement_type' => 'issue',
                    'reference_type' => 'stock_issue',
                    'reference_id' => $issue->id,
                    'movement_date' => $data->issuedAt,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'balance_after' => $qtyAfter,
                    'unit_cost' => null,
                    'created_by' => $actorId,
                    'note' => $data->note,
                ]);

                $snapshot = $snapshots[$productId];

                if ($qtyAfter <= $snapshot->minStock) {
                    $lowStock[] = [
                        'product_id' => $productId,
                        'product_name' => $snapshot->name,
                        'current_qty' => $qtyAfter,
                        'min_stock' => $snapshot->minStock,
                    ];
                }
            }

            $issue->update([
                'number' => $this->numbers->forIssue($issue->id, $issue->issued_at),
            ]);

            return [$issue, $lowStock];
        });

        $issue->load('items');

        StockIssued::dispatch(
            issueId: $issue->id,
            number: (string) $issue->number,
            items: $issue->items
                ->map(fn ($item): array => [
                    'product_id' => (int) $item->product_id,
                    'qty' => (float) $item->qty,
                ])
                ->all(),
            issuedAt: $issue->issued_at->format('Y-m-d'),
            actorId: $actorId,
        );

        foreach ($lowStock as $item) {
            LowStockDetected::dispatch(
                productId: $item['product_id'],
                productName: $item['product_name'],
                currentQty: $item['current_qty'],
                minStock: $item['min_stock'],
                detectedAt: now()->toIso8601String(),
            );
        }

        return $issue;
    }

    /**
     * @return array<int, ProductSnapshot> keyed by product id
     */
    private function productSnapshots(StockIssueData $data): array
    {
        $snapshots = $this->products->findMany($data->productIds());

        foreach ($data->productIds() as $productId) {
            if (! isset($snapshots[$productId])) {
                throw new ProductUnavailableException($productId);
            }
        }

        return $snapshots;
    }

    /**
     * @param  array<int, array{product_id: int, qty: float}>  $items
     * @return array<int, array{product_id: int, qty: float}>
     */
    private function sortedItems(array $items): array
    {
        usort($items, static fn (array $a, array $b): int => $a['product_id'] <=> $b['product_id']);

        return $items;
    }
}
