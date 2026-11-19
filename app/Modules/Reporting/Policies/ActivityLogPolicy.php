<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Policies;

use App\Modules\Reporting\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivityLogPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(Authenticatable $user, ActivityLog $activityLog): bool
    {
        return $user->hasRole('admin');
    }
}
