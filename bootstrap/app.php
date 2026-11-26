<?php

use App\Modules\Auth\Http\Middleware\EnsureUserHasRole;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Product\Exceptions\ProductUnavailableException;
use App\Modules\Supplier\Exceptions\SupplierUnavailableException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Error bisnis sudah punya render() sendiri; jangan mengisi log dengan noise.
        $exceptions->dontReport([
            InsufficientStockException::class,
            ProductUnavailableException::class,
            SupplierUnavailableException::class,
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
