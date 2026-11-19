<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'qty',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
        ];
    }
}
