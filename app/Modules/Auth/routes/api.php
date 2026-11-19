<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\Api\ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware('api')
    ->group(function (): void {
        Route::post('/auth/login', [ApiAuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('api.auth.login');

        Route::post('/auth/logout', [ApiAuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('api.auth.logout');

        Route::get('/me', [ApiAuthController::class, 'me'])
            ->middleware('auth:sanctum')
            ->name('api.me');
    });
