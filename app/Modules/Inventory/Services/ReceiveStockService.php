<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTO\StockReceiptData;
use App\Modules\Inventory\Events\LowStockDetected;
use App\Modules\Inventory\Events\StockReceived;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use App\Modules\Product\Exceptions\ProductUnavailableException;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\Exceptions\SupplierUnavailableException;
use Illuminate\Support\Facades\DB;

final class ReceiveStockService
{
    public function __construct(
        private readonly SupplierDirectoryContract $suppliers,
        private readonly ProductCatalogContract $products,
        private readonly StockNumberGenerator $numbers,
    ) {}

    public function handle(StockReceiptData $data, int $actorId): StockReceipt
    {
        if ($this->suppliers->find($data->supplierId) === null) {
            throw new SupplierUnavailableException($data->supplierId);
        }

        $snapshots = $this->productSnapshots($data);

        [$receipt, $lowStock] = DB::transaction(function () use ($data, $actorId, $snapshots): array {
            $receipt = StockReceipt::query()->create([
                'number' => null,
                'supplier_id' => $data->supplierId,
                'received_at' => $data->receivedAt,
                'total_amount' => 0,
                'created_by' => $actorId,
                'note' => $data->note,
            ]);

            $total = 0.0;
            $lowStock = [];

            foreach ($this->sortedItems($data->items) as $item) {
                $productId = (int) $item['product_id'];
                $qty = (float) $item['qty'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = round($qty * $unitCost, 2);

                $receipt->items()->create([
                    'product_id' => $productId,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $balance = StockBalance::query()
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $qtyAfter = round((float) $balance->qty + $qty, 3);
                $balance->update(['qty' => $qtyAfter]);

                StockMovement::query()->create([
                    'product_id' => $productId,
                    'movement_type' => 'receipt',
                    'reference_type' => 'stock_receipt',
                    'reference_id' => $receipt->id,
                    'movement_date' => $data->receivedAt,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'balance_after' => $qtyAfter,
                    'unit_cost' => $unitCost,
                    'created_by' => $actorId,
                    'note' => $data->note,
                ]);

                $total += $subtotal;

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

            $receipt->update([
                'number' => $this->numbers->forReceipt($receipt->id, $receipt->received_at),
                'total_amount' => round($total, 2),
            ]);

            return [$receipt, $lowStock];
        });

        $receipt->load('items');

        StockReceived::dispatch(
            receiptId: $receipt->id,
            number: (string) $receipt->number,
            supplierId: $receipt->supplier_id,
            items: $receipt->items
                ->map(fn ($item): array => [
                    'product_id' => (int) $item->product_id,
                    'qty' => (float) $item->qty,
                    'unit_cost' => (float) $item->unit_cost,
                ])
                ->all(),
            receivedAt: $receipt->received_at->format('Y-m-d'),
            actorId: $actorId,
        );

        $this->dispatchLowStock($lowStock);

        return $receipt;
    }

    /**
     * @return array<int, ProductSnapshot> keyed by product id
     */
    private function productSnapshots(StockReceiptData $data): array
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
     * @param  array<int, array{product_id: int, qty: float, unit_cost: float}>  $items
     * @return array<int, array{product_id: int, qty: float, unit_cost: float}>
     */
    private function sortedItems(array $items): array
    {
        usort($items, static fn (array $a, array $b): int => $a['product_id'] <=> $b['product_id']);

        return $items;
    }

    /**
     * @param  array<int, array{product_id: int, product_name: string, current_qty: float, min_stock: float}>  $lowStock
     */
    private function dispatchLowStock(array $lowStock): void
    {
        foreach ($lowStock as $item) {
            LowStockDetected::dispatch(
                productId: $item['product_id'],
                productName: $item['product_name'],
                currentQty: $item['current_qty'],
                minStock: $item['min_stock'],
                detectedAt: now()->toIso8601String(),
            );
        }
    }
}
