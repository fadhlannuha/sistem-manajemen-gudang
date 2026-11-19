<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\Contracts\InventoryQueryContract;
use App\Modules\Inventory\Listeners\CreateStockBalance;
use App\Modules\Inventory\Models\StockIssue;
use App\Modules\Inventory\Models\StockReceipt;
use App\Modules\Inventory\Policies\StockIssuePolicy;
use App\Modules\Inventory\Policies\StockReceiptPolicy;
use App\Modules\Inventory\Services\InventoryQueryService;
use App\Modules\Product\Events\ProductCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryQueryContract::class, InventoryQueryService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inventory');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        Gate::policy(StockReceipt::class, StockReceiptPolicy::class);
        Gate::policy(StockIssue::class, StockIssuePolicy::class);

        Event::listen(ProductCreated::class, CreateStockBalance::class);
    }
}
