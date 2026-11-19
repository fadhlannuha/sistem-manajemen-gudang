<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        User::factory()->staff()->create([
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'STAFF@example.test',
            'password' => 'rahasia123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'roles'],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'staff@example.test')
            ->assertJsonPath('user.roles', ['staff_gudang']);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => User::query()->where('email', 'staff@example.test')->value('id'),
        ]);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        User::factory()->staff()->create([
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.test',
            'password' => 'password-salah',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_api_requires_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_api_me_returns_authenticated_user(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.test',
            'password' => 'rahasia123',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.test',
            'password' => 'rahasia123',
        ])->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', 'admin@example.test')
            ->assertJsonPath('roles', ['admin']);
    }

    public function test_api_logout_revokes_token(): void
    {
        User::factory()->staff()->create([
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
