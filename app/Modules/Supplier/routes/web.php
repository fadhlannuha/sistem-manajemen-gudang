<?php

declare(strict_types=1);

use App\Modules\Supplier\Http\Controllers\PaymentTermController;
use App\Modules\Supplier\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])
        ->middleware('role:admin')
        ->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])
        ->middleware('role:admin')
        ->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
        ->middleware('role:admin')
        ->name('suppliers.edit');
    Route::match(['put', 'patch'], '/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware('role:admin')
        ->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('suppliers.destroy');

    Route::get('/payment-terms', [PaymentTermController::class, 'index'])->name('payment-terms.index');
    Route::get('/payment-terms/create', [PaymentTermController::class, 'create'])
        ->middleware('role:admin')
        ->name('payment-terms.create');
    Route::post('/payment-terms', [PaymentTermController::class, 'store'])
        ->middleware('role:admin')
        ->name('payment-terms.store');
    Route::get('/payment-terms/{payment_term}/edit', [PaymentTermController::class, 'edit'])
        ->middleware('role:admin')
        ->name('payment-terms.edit');
    Route::match(['put', 'patch'], '/payment-terms/{payment_term}', [PaymentTermController::class, 'update'])
        ->middleware('role:admin')
        ->name('payment-terms.update');
    Route::delete('/payment-terms/{payment_term}', [PaymentTermController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('payment-terms.destroy');
});
