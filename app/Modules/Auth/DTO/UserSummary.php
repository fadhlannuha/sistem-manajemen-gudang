<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

final readonly class UserSummary
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public array $roles = [],
    ) {}
}
