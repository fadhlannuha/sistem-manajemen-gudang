<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/_test/admin-only', fn (): string => 'admin-only')
            ->name('test.admin-only');

        Route::middleware(['web', 'auth', 'role:admin,staff_gudang'])
            ->get('/_test/stock-transactions', fn (): string => 'stock-transactions')
            ->name('test.stock-transactions');

        Route::middleware('role:admin')
            ->get('/_test/role-without-auth', fn (): string => 'unreachable')
            ->name('test.role-without-auth');

        Route::getRoutes()->refreshNameLookups();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('test.admin-only'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_admin_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('test.admin-only'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('test.admin-only'))
            ->assertOk()
            ->assertSee('admin-only');
    }

    public function test_staff_can_access_stock_transaction_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get(route('test.stock-transactions'))
            ->assertOk()
            ->assertSee('stock-transactions');
    }

    public function test_role_middleware_rejects_guest_with_401(): void
    {
        $this->get(route('test.role-without-auth'))->assertStatus(401);
    }

    public function test_blade_role_directive_checks_authenticated_user(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        $template = "@role('admin') Admin @else Bukan Admin @endrole";

        $this->actingAs($admin);
        $this->assertSame('Admin', trim(Blade::render($template)));

        $this->actingAs($staff);
        $this->assertSame('Bukan Admin', trim(Blade::render($template)));
    }
}
