<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = app(ProductService::class);
    }

    public function test_calculated_stock_after_purchase(): void
    {
        $product = Product::factory()->create(['current_stock' => 0]);

        ProductBatch::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100,
            'quantity_available' => 100,
        ]);

        $this->assertEquals(100, $product->fresh()->getCalculatedStock());
    }

    public function test_calculated_stock_after_sale(): void
    {
        $product = Product::factory()->create(['current_stock' => 0]);

        ProductBatch::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100,
            'quantity_available' => 70,
            'quantity_sold' => 30,
        ]);

        $this->assertEquals(70, $product->fresh()->getCalculatedStock());
    }

    public function test_product_creation(): void
    {
        $existing = Product::factory()->create();
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001',
            'category_id' => $existing->category_id,
            'brand_id' => $existing->brand_id,
            'unit_id' => $existing->unit_id,
            'alert_quantity' => 10,
        ];

        $created = $this->productService->createProduct($data);

        $this->assertNotNull($created->id);
        $this->assertEquals('Test Product', $created->name);
        $this->assertEquals('TEST-SKU-001', $created->sku);
    }
}
