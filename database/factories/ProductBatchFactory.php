<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductBatch>
 */
class ProductBatchFactory extends Factory
{
    protected $model = ProductBatch::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 10, 500);

        return [
            'product_id' => Product::factory(),
            'batch_number' => fake()->unique()->bothify('BATCH-####'),
            'status' => ProductBatch::STATUS_ACTIVE,
            'quantity' => $quantity,
            'quantity_available' => $quantity,
            'quantity_sold' => 0,
            'quantity_returned' => 0,
            'quantity_adjusted' => 0,
        ];
    }
}
