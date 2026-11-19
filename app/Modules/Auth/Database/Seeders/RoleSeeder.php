<?php

declare(strict_types=1);

namespace App\Modules\Auth\Database\Seeders;

use App\Modules\Auth\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['name' => 'admin'],
            ['label' => 'Administrator'],
        );

        Role::query()->updateOrCreate(
            ['name' => 'staff_gudang'],
            ['label' => 'Staff Gudang'],
        );
    }
}
