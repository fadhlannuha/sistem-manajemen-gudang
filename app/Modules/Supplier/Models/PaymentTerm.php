<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Models;

use App\Modules\Supplier\Database\Factories\PaymentTermFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTerm extends Model
{
    /** @use HasFactory<PaymentTermFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'days',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days' => 'integer',
        ];
    }

    protected static function newFactory(): PaymentTermFactory
    {
        return PaymentTermFactory::new();
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
