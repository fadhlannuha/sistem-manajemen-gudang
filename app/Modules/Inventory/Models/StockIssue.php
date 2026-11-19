<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\StockIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssue extends Model
{
    /** @use HasFactory<StockIssueFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'issued_at',
        'destination',
        'created_by',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
        ];
    }

    protected static function newFactory(): StockIssueFactory
    {
        return StockIssueFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockIssueItem::class);
    }
}
