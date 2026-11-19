<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Contracts;

use App\Modules\Inventory\DTO\LowStockView;
use App\Modules\Inventory\DTO\StockBalanceView;
use App\Modules\Inventory\DTO\StockMovementView;

interface InventoryQueryContract
{
    public function balanceFor(int $productId): ?StockBalanceView;

    /**
     * @param  array<int, int>  $productIds  kosong = semua
     * @return array<int, StockBalanceView> keyed by product id
     */
    public function balances(array $productIds = []): array;

    /**
     * @return array<int, StockMovementView>
     */
    public function movementsFor(
        int $productId,
        ?string $from = null,
        ?string $to = null,
    ): array;

    /**
     * @return array<int, StockMovementView>
     */
    public function recentMovements(int $limit = 5): array;

    /**
     * @return array<int, LowStockView>
     */
    public function lowStock(int $limit = 20): array;

    public function countLowStock(): int;

    public function hasMovements(int $productId): bool;
}
