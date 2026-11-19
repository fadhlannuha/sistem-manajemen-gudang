<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Database\Seeders;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Inventory\DTO\StockIssueData;
use App\Modules\Inventory\DTO\StockReceiptData;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Inventory\Services\IssueStockService;
use App\Modules\Inventory\Services\ReceiveStockService;
use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\DTO\ProductSnapshot;
use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\DTO\SupplierSnapshot;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function __construct(
        private readonly ReceiveStockService $receipts,
        private readonly IssueStockService $issues,
        private readonly ProductCatalogContract $products,
        private readonly SupplierDirectoryContract $suppliers,
        private readonly UserDirectoryContract $users,
    ) {}

    public function run(): void
    {
        $products = $this->products->search('', 1000);

        foreach ($products as $product) {
            StockBalance::query()->firstOrCreate(
                ['product_id' => $product->id],
                ['qty' => 0],
            );
        }

        if ($products === [] || StockReceipt::query()->exists() || StockIssue::query()->exists()) {
            return;
        }

        $actorId = $this->adminId();

        if ($actorId === null) {
            return;
        }

        $productsBySku = [];
        foreach ($products as $product) {
            $productsBySku[$product->sku] = $product;
        }

        $suppliersByCode = [];
        foreach ($this->suppliers->search('', 100) as $supplier) {
            $suppliersByCode[$supplier->code] = $supplier;
        }

        $this->seedReceipts($productsBySku, $suppliersByCode, $actorId);
        $this->seedIssues($productsBySku, $actorId);
    }

    /**
     * @param  array<string, ProductSnapshot>  $products
     * @param  array<string, SupplierSnapshot>  $suppliers
     */
    private function seedReceipts(array $products, array $suppliers, int $actorId): void
    {
        $this->receive(
            supplier: $suppliers['SUP-001'] ?? null,
            date: now()->subDays(20)->toDateString(),
            items: $this->receiptItems($products, [
                ['PRD-0005', 20, 95000],
                ['PRD-0006', 15, 45000],
                ['PRD-0007', 20, 120000],
                ['PRD-0008', 15, 150000],
                ['PRD-0009', 30, 85000],
                ['PRD-0010', 12, 180000],
                ['PRD-0019', 60, 6000],
                ['PRD-0020', 40, 8000],
            ]),
            actorId: $actorId,
        );

        $this->receive(
            supplier: $suppliers['SUP-002'] ?? null,
            date: now()->subDays(15)->toDateString(),
            items: $this->receiptItems($products, [
                ['PRD-0001', 100, 45000],
                ['PRD-0002', 80, 3500],
                ['PRD-0003', 50, 12000],
                ['PRD-0004', 40, 8000],
            ]),
            actorId: $actorId,
        );

        $this->receive(
            supplier: $suppliers['SUP-003'] ?? null,
            date: now()->subDays(10)->toDateString(),
            items: $this->receiptItems($products, [
                ['PRD-0011', 40, 18000],
                ['PRD-0012', 40, 17000],
                ['PRD-0013', 60, 15000],
                ['PRD-0014', 30, 12000],
            ]),
            actorId: $actorId,
        );
    }

    /**
     * @param  array<string, ProductSnapshot>  $products
     */
    private function seedIssues(array $products, int $actorId): void
    {
        $this->issue(
            date: now()->subDays(7)->toDateString(),
            destination: 'Divisi Administrasi',
            items: $this->issueItems($products, [
                ['PRD-0001', 20],
                ['PRD-0002', 30],
                ['PRD-0004', 15],
            ]),
            actorId: $actorId,
        );

        $this->issue(
            date: now()->subDays(3)->toDateString(),
            destination: 'Divisi Umum',
            items: $this->issueItems($products, [
                ['PRD-0005', 16],
                ['PRD-0006', 5],
                ['PRD-0011', 10],
                ['PRD-0013', 12],
            ]),
            actorId: $actorId,
        );
    }

    private function receive(?SupplierSnapshot $supplier, string $date, array $items, int $actorId): void
    {
        if ($supplier === null || $items === []) {
            return;
        }

        $this->receipts->handle(
            new StockReceiptData(
                supplierId: $supplier->id,
                receivedAt: $date,
                note: 'Data demo penerimaan stok.',
                items: $items,
            ),
            $actorId,
        );
    }

    private function issue(string $date, string $destination, array $items, int $actorId): void
    {
        if ($items === []) {
            return;
        }

        $this->issues->handle(
            new StockIssueData(
                issuedAt: $date,
                destination: $destination,
                note: 'Data demo pengeluaran stok.',
                items: $items,
            ),
            $actorId,
        );
    }

    /**
     * @param  array<string, ProductSnapshot>  $products
     * @param  array<int, array{0: string, 1: float, 2: float}>  $definitions
     * @return array<int, array{product_id: int, qty: float, unit_cost: float}>
     */
    private function receiptItems(array $products, array $definitions): array
    {
        $items = [];

        foreach ($definitions as [$sku, $qty, $unitCost]) {
            if (! isset($products[$sku])) {
                continue;
            }

            $items[] = [
                'product_id' => $products[$sku]->id,
                'qty' => (float) $qty,
                'unit_cost' => (float) $unitCost,
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, ProductSnapshot>  $products
     * @param  array<int, array{0: string, 1: float}>  $definitions
     * @return array<int, array{product_id: int, qty: float}>
     */
    private function issueItems(array $products, array $definitions): array
    {
        $items = [];

        foreach ($definitions as [$sku, $qty]) {
            if (! isset($products[$sku])) {
                continue;
            }

            $items[] = [
                'product_id' => $products[$sku]->id,
                'qty' => (float) $qty,
            ];
        }

        return $items;
    }

    private function adminId(): ?int
    {
        $count = $this->users->count();

        if ($count === 0) {
            return null;
        }

        $users = $this->users->findMany(range(1, $count));

        foreach ($users as $user) {
            if (in_array('admin', $user->roles, true)) {
                return $user->id;
            }
        }

        return array_key_first($users);
    }
}
