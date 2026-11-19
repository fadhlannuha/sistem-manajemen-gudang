<?php

declare(strict_types=1);

namespace App\Modules\Product\Policies;

use App\Modules\Product\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, Product $product): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(Authenticatable $user, Product $product): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(Authenticatable $user, Product $product): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(Authenticatable $user, Product $product): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, Product $product): bool
    {
        return false;
    }
}
