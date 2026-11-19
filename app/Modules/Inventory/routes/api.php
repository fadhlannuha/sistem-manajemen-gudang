<?php

declare(strict_types=1);

use App\Modules\Inventory\Http\Controllers\Api\InventoryApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware('api')
    ->group(function (): void {
        Route::post('/inventory/receipts', [InventoryApiController::class, 'storeReceipt'])
            ->middleware(['auth:sanctum', 'role:admin,staff_gudang'])
            ->name('api.inventory.receipts.store');

        Route::post('/inventory/issues', [InventoryApiController::class, 'storeIssue'])
            ->middleware(['auth:sanctum', 'role:admin,staff_gudang'])
            ->name('api.inventory.issues.store');

        Route::get('/inventory/balances', [InventoryApiController::class, 'balances'])
            ->middleware('auth:sanctum')
            ->name('api.inventory.balances');

        Route::get('/inventory/products/{productId}/movements', [InventoryApiController::class, 'movements'])
            ->middleware('auth:sanctum')
            ->name('api.inventory.movements');
    });
