<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;

final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inventory');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
    }
}
