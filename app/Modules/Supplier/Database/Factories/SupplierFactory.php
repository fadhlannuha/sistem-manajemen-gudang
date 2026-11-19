<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Database\Factories;

use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('SUP-###'),
            'name' => 'PT '.fake()->unique()->company(),
            'contact_name' => fake()->name(),
            'phone' => fake()->numerify('+62 21 5550###'),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'payment_term_id' => PaymentTerm::factory(),
        ];
    }
}
