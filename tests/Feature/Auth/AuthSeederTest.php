<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_roles_and_demo_users(): void
    {
        $this->seed();

        $this->assertSame(2, Role::query()->count());
        $this->assertSame(3, User::query()->count());

        $this->assertDatabaseHas('roles', ['name' => 'admin', 'label' => 'Administrator']);
        $this->assertDatabaseHas('roles', ['name' => 'staff_gudang', 'label' => 'Staff Gudang']);

        $admin = User::query()->where('email', 'admin@warehouse.test')->firstOrFail();
        $staff = User::query()->where('email', 'staff@warehouse.test')->firstOrFail();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('staff_gudang'));
        $this->assertTrue($staff->hasRole('staff_gudang'));
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertDatabaseCount('role_user', 3);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(2, Role::query()->count());
        $this->assertSame(3, User::query()->count());
        $this->assertDatabaseCount('role_user', 3);
    }
}
