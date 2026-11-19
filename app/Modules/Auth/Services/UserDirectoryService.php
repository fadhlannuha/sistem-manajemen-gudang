<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\UserDirectoryContract;
use App\Modules\Auth\DTO\UserSummary;
use App\Modules\Auth\Models\User;

final class UserDirectoryService implements UserDirectoryContract
{
    public function find(int $userId): ?UserSummary
    {
        $user = User::query()->with('roles:id,name')->find($userId);

        return $user === null ? null : $this->toSummary($user);
    }

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, UserSummary>
     */
    public function findMany(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return User::query()
            ->with('roles:id,name')
            ->whereIn('id', $userIds)
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->id => $this->toSummary($user)])
            ->all();
    }

    public function count(): int
    {
        return User::query()->count();
    }

    private function toSummary(User $user): UserSummary
    {
        return new UserSummary(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            roles: $user->roles->pluck('name')->all(),
        );
    }
}
