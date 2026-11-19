<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Providers;

use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Inventory\Events\LowStockDetected;
use App\Modules\Inventory\Events\StockIssued;
use App\Modules\Inventory\Events\StockReceived;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeactivated;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Reporting\Listeners\RecordActivityLog;
use App\Modules\Reporting\Models\ActivityLog;
use App\Modules\Reporting\Policies\ActivityLogPolicy;
use App\Modules\Supplier\Events\SupplierCreated;
use App\Modules\Supplier\Events\SupplierDeactivated;
use App\Modules\Supplier\Events\SupplierUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ReportingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reporting');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);

        Event::listen(
            UserRegistered::class,
            RecordActivityLog::class,
        );
        Event::listen(
            ProductCreated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            ProductUpdated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            ProductDeactivated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            SupplierCreated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            SupplierUpdated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            SupplierDeactivated::class,
            RecordActivityLog::class,
        );
        Event::listen(
            StockReceived::class,
            RecordActivityLog::class,
        );
        Event::listen(
            StockIssued::class,
            RecordActivityLog::class,
        );
        Event::listen(
            LowStockDetected::class,
            RecordActivityLog::class,
        );
    }
}
