<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Inventory\Models\StockReceipt;
use Illuminate\Contracts\Auth\Authenticatable;

class StockReceiptPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, StockReceipt $receipt): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, StockReceipt $receipt): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, StockReceipt $receipt): bool
    {
        return false;
    }

    public function restore(Authenticatable $user, StockReceipt $receipt): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, StockReceipt $receipt): bool
    {
        return false;
    }
}
