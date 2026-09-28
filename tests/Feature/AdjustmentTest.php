<?php

namespace Tests\Feature;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Enums\AdjustmentType;
use App\Services\AdjustmentService;
use App\Events\AdjustmentCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected ProductBatch $batch;
    protected AdjustmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->product = Product::factory()->create([
            'requires_batch_tracking' => true,
        ]);

        $this->batch = ProductBatch::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'quantity_available' => 10,
            'quantity_sold' => 0,
            'status' => 'active',
        ]);

        $this->product->updateStock();

        // Fake business events and broadcast events to avoid serialization of deleted models
        Event::fake([AdjustmentCreated::class, \App\Events\ModelChanged::class]);

        $this->service = app(AdjustmentService::class);
    }

    public function test_create_addition_adjustment_updates_stock()
    {
        $adjustment = $this->service->createAdjustment([
            'tracking_no' => 'ADJ-100',
            'adjustment_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_batch_id' => $this->batch->id,
                    'quantity' => 5,
                    'type' => AdjustmentType::Addition->value,
                ]
            ]
        ]);

        $this->assertNotNull($adjustment->id);
        $this->batch->refresh();
        $this->product->refresh();

        $this->assertEquals(15.0, (float) $this->batch->quantity);
        $this->assertEquals(15.0, (float) $this->batch->quantity_available);
        $this->assertEquals(15.0, (float) $this->product->current_stock);
    }

    public function test_create_subtraction_adjustment_updates_stock()
    {
        $adjustment = $this->service->createAdjustment([
            'tracking_no' => 'ADJ-101',
            'adjustment_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_batch_id' => $this->batch->id,
                    'quantity' => 4,
                    'type' => AdjustmentType::Subtraction->value,
                ]
            ]
        ]);

        $this->assertNotNull($adjustment->id);
        $this->batch->refresh();
        $this->product->refresh();

        $this->assertEquals(6.0, (float) $this->batch->quantity_available);
        $this->assertEquals(4.0, (float) $this->batch->quantity_sold);
        $this->assertEquals(6.0, (float) $this->product->current_stock);
    }

    public function test_update_adjustment_reverts_old_stock_and_applies_new()
    {
        // Create: add 3
        $adjustment = $this->service->createAdjustment([
            'tracking_no' => 'ADJ-102',
            'adjustment_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_batch_id' => $this->batch->id,
                    'quantity' => 3,
                    'type' => AdjustmentType::Addition->value,
                ]
            ]
        ]);

        $this->batch->refresh();
        $this->assertEquals(13.0, (float) $this->batch->quantity_available);

        // Update: change to subtract 2
        $this->service->updateAdjustment($adjustment, [
            'tracking_no' => 'ADJ-102',
            'adjustment_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_batch_id' => $this->batch->id,
                    'quantity' => 2,
                    'type' => AdjustmentType::Subtraction->value,
                ]
            ]
        ]);

        $this->batch->refresh();
        $this->product->refresh();

        // Original: 10 → add 3 = 13 → revert add 3 = 10 → subtract 2 = 8
        $this->assertEquals(8.0, (float) $this->batch->quantity_available);
        $this->assertEquals(2.0, (float) $this->batch->quantity_sold);
        $this->assertEquals(8.0, (float) $this->product->current_stock);
    }

    public function test_delete_adjustment_reverts_stock()
    {
        // Create: subtract 5
        $adjustment = $this->service->createAdjustment([
            'tracking_no' => 'ADJ-103',
            'adjustment_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_batch_id' => $this->batch->id,
                    'quantity' => 5,
                    'type' => AdjustmentType::Subtraction->value,
                ]
            ]
        ]);

        $this->batch->refresh();
        $this->assertEquals(5.0, (float) $this->batch->quantity_available);

        // Delete the adjustment
        $this->service->deleteAdjustment($adjustment);

        $this->batch->refresh();
        $this->product->refresh();

        // Should return back to original 10
        $this->assertEquals(10.0, (float) $this->batch->quantity_available);
        $this->assertEquals(0.0, (float) $this->batch->quantity_sold);
        $this->assertEquals(10.0, (float) $this->product->current_stock);
    }
}
