<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'label',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
