<?php

namespace Database\Seeders;

use App\Modules\Auth\Database\Seeders\RoleSeeder;
use App\Modules\Auth\Database\Seeders\UserSeeder;
use App\Modules\Inventory\Database\Seeders\InventorySeeder;
use App\Modules\Product\Database\Seeders\ProductSeeder;
use App\Modules\Supplier\Database\Seeders\SupplierSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            InventorySeeder::class,
        ]);
    }
}
