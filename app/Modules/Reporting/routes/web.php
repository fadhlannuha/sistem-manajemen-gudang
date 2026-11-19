<?php

declare(strict_types=1);

use App\Modules\Reporting\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
