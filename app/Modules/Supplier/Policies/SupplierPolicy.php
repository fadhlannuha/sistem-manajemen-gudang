<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Policies;

use App\Modules\Supplier\Models\Supplier;
use Illuminate\Contracts\Auth\Authenticatable;

class SupplierPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, Supplier $supplier): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(Authenticatable $user, Supplier $supplier): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(Authenticatable $user, Supplier $supplier): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(Authenticatable $user, Supplier $supplier): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, Supplier $supplier): bool
    {
        return false;
    }
}
