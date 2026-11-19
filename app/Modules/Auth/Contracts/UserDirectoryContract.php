<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\DTO\UserSummary;

interface UserDirectoryContract
{
    public function find(int $userId): ?UserSummary;

    /**
     * @param  array<int, int>  $userIds
     * @return array<int, UserSummary>
     */
    public function findMany(array $userIds): array;

    public function count(): int;
}
