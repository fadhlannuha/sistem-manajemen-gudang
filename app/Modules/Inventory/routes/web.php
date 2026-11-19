<?php

declare(strict_types=1);

use App\Modules\Inventory\Http\Controllers\StockBalanceController;
use App\Modules\Inventory\Http\Controllers\StockCardController;
use App\Modules\Inventory\Http\Controllers\StockIssueController;
use App\Modules\Inventory\Http\Controllers\StockReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/inventory/balances', [StockBalanceController::class, 'index'])
        ->name('inventory.balances.index');
    Route::get('/inventory/stock-card', [StockCardController::class, 'index'])
        ->name('inventory.stock-card.index');

    Route::get('/inventory/receipts', [StockReceiptController::class, 'index'])
        ->name('inventory.receipts.index');
    Route::get('/inventory/receipts/create', [StockReceiptController::class, 'create'])
        ->middleware('role:admin,staff_gudang')
        ->name('inventory.receipts.create');
    Route::post('/inventory/receipts', [StockReceiptController::class, 'store'])
        ->middleware('role:admin,staff_gudang')
        ->name('inventory.receipts.store');
    Route::get('/inventory/receipts/{receipt}', [StockReceiptController::class, 'show'])
        ->name('inventory.receipts.show');

    Route::get('/inventory/issues', [StockIssueController::class, 'index'])
        ->name('inventory.issues.index');
    Route::get('/inventory/issues/create', [StockIssueController::class, 'create'])
        ->middleware('role:admin,staff_gudang')
        ->name('inventory.issues.create');
    Route::post('/inventory/issues', [StockIssueController::class, 'store'])
        ->middleware('role:admin,staff_gudang')
        ->name('inventory.issues.store');
    Route::get('/inventory/issues/{issue}', [StockIssueController::class, 'show'])
        ->name('inventory.issues.show');
});
