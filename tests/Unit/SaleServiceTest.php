<?php

namespace Tests\Unit;

use App\Actions\Sales\CalculateSaleTotalsAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalculateSaleTotalsAction $calculateSaleTotalsAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculateSaleTotalsAction = app(CalculateSaleTotalsAction::class);
    }

    public function test_calculate_sale_totals(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $items = [
            [
                'product_id' => $product1->id,
                'quantity' => 2,
                'unit_price' => 100.00,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 3,
                'unit_price' => 50.00,
            ],
        ];

        $result = $this->calculateSaleTotalsAction->execute($items, 50.00);

        $this->assertEquals(350.00, $result['subtotal']);
        $this->assertEquals(50.00, $result['discount']);
        $this->assertEquals(300.00, $result['receivable']);
        $this->assertCount(2, $result['lines']);
    }

    public function test_discount_calculation_with_zero_floor(): void
    {
        $product = Product::factory()->create();

        $items = [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 100.00,
            ],
        ];

        $result = $this->calculateSaleTotalsAction->execute($items, 200.00);

        $this->assertEquals(100.00, $result['subtotal']);
        $this->assertEquals(0.00, $result['receivable']);
    }

    public function test_unit_discount_percentage_is_applied_to_the_line_subtotal(): void
    {
        $product = Product::factory()->create();

        $items = [
            [
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_price' => 50.00,
                'unit_discount' => 10.00,
                'unit_discount_type' => 'percentage',
            ],
        ];

        $result = $this->calculateSaleTotalsAction->execute($items, 0.00);

        $this->assertEquals(135.00, $result['subtotal']);
        $this->assertEquals(135.00, $result['receivable']);
    }

    public function test_sale_retrieval_with_customer_relationship(): void
    {
        $customer = Customer::factory()->create();

        $sale = Sale::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $retrieved = Sale::with('customer')->find($sale->id);

        $this->assertNotNull($retrieved->customer);
        $this->assertEquals($customer->id, $retrieved->customer->id);
    }

    public function test_calculate_sale_totals_with_free_items(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $items = [
            [
                'product_id' => $product1->id,
                'quantity' => 2,
                'unit_price' => 100.00,
                'is_free' => true,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 3,
                'unit_price' => 50.00,
                'is_free' => false,
            ],
        ];

        $result = $this->calculateSaleTotalsAction->execute($items, 0.00);

        $this->assertEquals(150.00, $result['subtotal']);
        $this->assertEquals(150.00, $result['receivable']);
        $this->assertTrue($result['lines'][0]['is_free']);
        $this->assertFalse($result['lines'][1]['is_free']);
        $this->assertEquals(0.00, $result['lines'][0]['line_total']);
        $this->assertEquals(150.00, $result['lines'][1]['line_total']);
    }
}
