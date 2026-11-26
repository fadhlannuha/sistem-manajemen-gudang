<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Database\Factories;

use App\Modules\Supplier\Models\PaymentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentTerm>
 */
class PaymentTermFactory extends Factory
{
    protected $model = PaymentTerm::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->unique()->words(2, true)),
            'days' => fake()->randomElement([0, 7, 14, 30]),
        ];
    }
}
