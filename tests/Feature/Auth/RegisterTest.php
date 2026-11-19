<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_staff_only(): void
    {
        $response = $this->post('/register', [
            'name' => 'Staff Baru',
            'email' => 'STAFF.BARU@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard.index'));

        $user = User::query()->where('email', 'staff.baru@example.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('staff_gudang'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'staff@example.test',
        ]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Staff Baru',
            'email' => 'staff@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(1, User::query()->count());
    }

    public function test_user_registered_event_is_dispatched(): void
    {
        Event::fake([UserRegistered::class]);

        $this->post('/register', [
            'name' => 'Staff Event',
            'email' => 'staff.event@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        Event::assertDispatched(
            UserRegistered::class,
            fn (UserRegistered $event): bool => $event->email === 'staff.event@example.test'
                && $event->roles === ['staff_gudang'],
        );
    }
}
