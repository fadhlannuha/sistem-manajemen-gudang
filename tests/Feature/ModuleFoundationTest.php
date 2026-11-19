<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Modules\Product\Providers\ProductServiceProvider;
use App\Modules\Reporting\Providers\ReportingServiceProvider;
use App\Modules\Supplier\Providers\SupplierServiceProvider;
use Tests\TestCase;

class ModuleFoundationTest extends TestCase
{
    /**
     * @return array<int, class-string>
     */
    private function moduleProviders(): array
    {
        return [
            AuthServiceProvider::class,
            ProductServiceProvider::class,
            SupplierServiceProvider::class,
            InventoryServiceProvider::class,
            ReportingServiceProvider::class,
        ];
    }

    public function test_module_service_providers_are_registered(): void
    {
        $registered = require base_path('bootstrap/providers.php');

        foreach ($this->moduleProviders() as $provider) {
            $this->assertContains($provider, $registered);
            $this->assertTrue(class_exists($provider));
        }
    }

    public function test_each_module_has_web_routes_and_provider(): void
    {
        foreach (['Auth', 'Product', 'Supplier', 'Inventory', 'Reporting'] as $module) {
            $this->assertDirectoryExists(app_path("Modules/{$module}"));
            $this->assertFileExists(app_path("Modules/{$module}/routes/web.php"));
            $this->assertFileExists(app_path("Modules/{$module}/Providers/{$module}ServiceProvider.php"));
        }
    }

    public function test_auth_module_owns_the_sanctum_token_migration(): void
    {
        $migrations = glob(app_path('Modules/Auth/Database/Migrations/*_create_personal_access_tokens_table.php'));

        $this->assertNotEmpty($migrations);
    }
}
