<?php

declare(strict_types=1);

namespace App\Modules\Product\Providers;

use App\Modules\Product\Contracts\ProductCatalogContract;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Policies\ProductPolicy;
use App\Modules\Product\Services\ProductCatalogService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductCatalogContract::class, ProductCatalogService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'product');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        Gate::policy(Product::class, ProductPolicy::class);
    }
}
