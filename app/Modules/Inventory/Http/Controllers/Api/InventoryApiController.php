<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers\Api;

use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\DTO\StockIssueData;
use App\Modules\Inventory\DTO\StockReceiptData;
use App\Modules\Inventory\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\StoreStockIssueRequest;
use App\Modules\Inventory\Http\Requests\StoreStockReceiptRequest;
use App\Modules\Inventory\Services\IssueStockService;
use App\Modules\Inventory\Services\ReceiveStockService;
use App\Modules\Product\Contracts\ProductCatalogContract;
use Illuminate\Http\JsonResponse;

class InventoryApiController extends Controller
{
    public function __construct(
        private readonly ReceiveStockService $receipts,
        private readonly IssueStockService $issues,
        private readonly InventoryQueryContract $inventory,
        private readonly ProductCatalogContract $products,
    ) {}

    public function storeReceipt(StoreStockReceiptRequest $request): JsonResponse
    {
        $receipt = $this->receipts->handle(
            $this->toReceiptData($request->validated()),
            (int) $request->user()->id,
        );

        return response()->json([
            'data' => [
                'id' => $receipt->id,
                'number' => $receipt->number,
            ],
        ], 201);
    }

    public function storeIssue(StoreStockIssueRequest $request): JsonResponse
    {
        $issue = $this->issues->handle(
            $this->toIssueData($request->validated()),
            (int) $request->user()->id,
        );

        return response()->json([
            'data' => [
                'id' => $issue->id,
                'number' => $issue->number,
            ],
        ], 201);
    }

    public function balances(): JsonResponse
    {
        return response()->json([
            'data' => array_values($this->inventory->balances()),
        ]);
    }

    public function movements(int $productId): JsonResponse
    {
        if ($this->products->findIncludingDeactivated($productId) === null) {
            return response()->json(['message' => 'Barang tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $this->inventory->movementsFor($productId),
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function toReceiptData(array $validated): StockReceiptData
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
     * @param  array<string, mixed>  $validated
     */
    private function toIssueData(array $validated): StockIssueData
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
