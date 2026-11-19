<?php

declare(strict_types=1);

namespace App\Modules\Auth\Database\Factories;

use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::query()->firstOrCreate(
                ['name' => 'admin'],
                ['label' => 'Administrator'],
            );

            $user->roles()->sync([$role->id]);
        });
    }

    public function staff(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::query()->firstOrCreate(
                ['name' => 'staff_gudang'],
                ['label' => 'Staff Gudang'],
            );

            $user->roles()->sync([$role->id]);
        });
    }
}
