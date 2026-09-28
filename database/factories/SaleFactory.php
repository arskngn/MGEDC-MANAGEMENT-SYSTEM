<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);

        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'invoice_no' => 'INV-'.fake()->unique()->numerify('######'),
            'sale_date' => now()->toDateString(),
            'note' => null,
            'subtotal' => $subtotal,
            'discount' => 0,
            'receivable_amount' => $subtotal,
            'paid_amount' => 0,
        ];
    }
}
