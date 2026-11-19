<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class UserRegistered implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public array $roles = [],
    ) {}
}
