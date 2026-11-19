<?php

declare(strict_types=1);

use App\Modules\Product\Http\Controllers\CategoryController;
use App\Modules\Product\Http\Controllers\ProductController;
use App\Modules\Product\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])
        ->middleware('role:admin')
        ->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('role:admin')
        ->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('role:admin')
        ->name('products.edit');
    Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update'])
        ->middleware('role:admin')
        ->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('products.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])
        ->middleware('role:admin')
        ->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('role:admin')
        ->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
        ->middleware('role:admin')
        ->name('categories.edit');
    Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('role:admin')
        ->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('categories.destroy');

    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::get('/units/create', [UnitController::class, 'create'])
        ->middleware('role:admin')
        ->name('units.create');
    Route::post('/units', [UnitController::class, 'store'])
        ->middleware('role:admin')
        ->name('units.store');
    Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])
        ->middleware('role:admin')
        ->name('units.edit');
    Route::match(['put', 'patch'], '/units/{unit}', [UnitController::class, 'update'])
        ->middleware('role:admin')
        ->name('units.update');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('units.destroy');
});
