<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee('Daftar sebagai staff gudang');
    }

    public function test_guest_can_view_register_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar Akun')
            ->assertSee('Staff Gudang');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->staff()->create([
            'name' => 'Staff Dashboard',
            'email' => 'staff.dashboard@example.test',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Staff Dashboard')
            ->assertSee('Total Barang')
            ->assertSee('Staff Gudang');
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->staff()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_unknown_page_shows_informative_404_page(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
