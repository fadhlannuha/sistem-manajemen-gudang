<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\StockReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReceipt extends Model
{
    /** @use HasFactory<StockReceiptFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'supplier_id',
        'received_at',
        'total_amount',
        'created_by',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function newFactory(): StockReceiptFactory
    {
        return StockReceiptFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }
}
