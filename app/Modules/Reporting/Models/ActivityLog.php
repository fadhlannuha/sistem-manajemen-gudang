<?php

declare(strict_types=1);

namespace App\Modules\Reporting\Models;

use App\Modules\Reporting\Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_id',
        'actor_name',
        'event',
        'subject_type',
        'subject_id',
        'description',
        'payload',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ActivityLogFactory
    {
        return ActivityLogFactory::new();
    }
}
