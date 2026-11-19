<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Providers;

use App\Modules\Supplier\Contracts\SupplierDirectoryContract;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Policies\SupplierPolicy;
use App\Modules\Supplier\Services\SupplierCatalogService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class SupplierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierDirectoryContract::class, SupplierCatalogService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'supplier');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        Gate::policy(Supplier::class, SupplierPolicy::class);
    }
}
