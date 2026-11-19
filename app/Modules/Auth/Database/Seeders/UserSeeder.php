<?php

declare(strict_types=1);

namespace App\Modules\Auth\Database\Seeders;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()
            ->whereIn('name', ['admin', 'staff_gudang'])
            ->pluck('id', 'name');

        $accounts = [
            ['name' => 'Admin Gudang', 'email' => 'admin@warehouse.test', 'role' => 'admin'],
            ['name' => 'Staff Gudang', 'email' => 'staff@warehouse.test', 'role' => 'staff_gudang'],
            ['name' => 'Staff Gudang Dua', 'email' => 'staff2@warehouse.test', 'role' => 'staff_gudang'],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            $roleId = $roles->get($account['role']);

            if ($roleId !== null) {
                $user->roles()->sync([$roleId]);
            }
        }
    }
}
