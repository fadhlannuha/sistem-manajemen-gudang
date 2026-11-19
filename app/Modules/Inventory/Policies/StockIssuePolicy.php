<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Inventory\Models\StockIssue;
use Illuminate\Contracts\Auth\Authenticatable;

class StockIssuePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, StockIssue $issue): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, StockIssue $issue): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, StockIssue $issue): bool
    {
        return false;
    }

    public function restore(Authenticatable $user, StockIssue $issue): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, StockIssue $issue): bool
    {
        return false;
    }
}
