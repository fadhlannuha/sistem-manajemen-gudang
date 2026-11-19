<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('login.store');

        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:6,1')
            ->name('register.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');
});
