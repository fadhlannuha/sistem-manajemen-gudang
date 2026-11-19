<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->staff()->create([
            'email' => 'staff@example.test',
        ]);

        $response = $this->post('/login', [
            'email' => 'STAFF@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->staff()->create([
            'email' => 'staff@example.test',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'staff@example.test',
            'password' => 'password-salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->staff()->create([
            'email' => 'staff@example.test',
        ]);

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->post('/login', [
                'email' => 'staff@example.test',
                'password' => 'password-salah',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'staff@example.test',
            'password' => 'password-salah',
        ]);

        $response->assertStatus(429);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
