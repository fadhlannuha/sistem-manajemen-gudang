<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Auth\Services\UserDirectoryService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserDirectoryContract::class, UserDirectoryService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'auth');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (is_file(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        Blade::if(
            'role',
            fn (string ...$roles): bool => auth()->user()?->hasAnyRole($roles) ?? false,
        );
    }
}
