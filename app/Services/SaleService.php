<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SaleItemBatch;
use App\Events\SaleCreated;
use App\Exceptions\InsufficientStockException;
use App\Events\SalePaymentReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\BatchManagementService;

class SaleService
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }
    /**
     * Store a new sale.
     */
    public function createSale(array $data): Sale
    {
        $sale = DB::transaction(function () use ($data) {
            $discountType = $data['discount_type'] ?? 'fixed';
            $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0), $discountType);

            $sale = Sale::create([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? Auth::id(),
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'discount_type' => $discountType,
                'receivable_amount' => $totals['receivable'],
                'paid_amount' => 0,
            ]);

            foreach ($data['items'] as $itemIndex => $itemData) {
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->firstOrFail();
                $quantityToSell = (float)$itemData['quantity'];
                
                if ($this->usesAutomaticBatchAllocation($itemData)) {
                    $cogsResult = $this->deductStockAndCalculateCogs(
                        $product,
                        $quantityToSell,
                        $sale->id
                    );
                } else {
                    $cogsResult = $this->deductFromSelectedBatches(
                        $product,
                        $itemData['batches'] ?? [],
                        $quantityToSell,
                        $sale->id
                    );
                }

                $lineDiscountRate = max((float) ($itemData['discount_rate'] ?? 0), 0);
                $lineDiscountType = $itemData['discount_type'] ?? 'fixed';
                $lineSubTotal = round($quantityToSell * (float)$itemData['unit_price'], 2);
                if ($lineDiscountType === 'percentage') {
                    $lineDiscountRate = min($lineDiscountRate, 100);
                    $lineDiscountAmount = round($lineSubTotal * $lineDiscountRate / 100, 2);
                } else {
                    $lineDiscountAmount = round($lineDiscountRate, 2);
                }
                $lineTotal = round(max(0, $lineSubTotal - $lineDiscountAmount), 2);

                $useFefo = $this->usesAutomaticBatchAllocation($itemData);
                $saleItem = SaleItem::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantityToSell,
                    'unit_label' => $product->unit->short_name ?? $product->unit->name,
                    'unit_price' => $itemData['unit_price'],
                    'discount_rate' => $lineDiscountRate,
                    'discount_type' => $lineDiscountType,
                    'line_total' => $lineTotal,
                    'cost_price' => $cogsResult['cost_price'],
                    'sale_id' => $sale->id,
                    'use_fefo' => $useFefo,
                ]);

                foreach ($cogsResult['deductions'] as $deduction) {
                    SaleItemBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'product_batch_id' => $deduction['batch_id'],
                        'quantity' => $deduction['quantity'],
                        'cost_price' => $deduction['cost_price_per_unit'],
                    ]);
                }
            }

            return $sale;
        });

        // Wrapped in try-catch so a broadcast/WebSocket failure never crashes the sale
        try {
            event(new SaleCreated($sale));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SaleCreated broadcast failed (sale was saved): ' . $e->getMessage());
        }

        return $sale;
    }

    protected function deductFromSelectedBatches(Product $product, array $selectedBatches, float $quantityToSell, int $saleId): array
    {
        $totalCost = 0;
        $totalDeducted = 0;
        $deductions = [];

        foreach ($selectedBatches as $batchData) {
            $batchId = $batchData['batch_id'] ?? null;
            $batchQty = (float)($batchData['quantity'] ?? 0);
            
            if (!$batchId || $batchQty <= 0) continue;

            $batch = ProductBatch::where('id', $batchId)
                ->where('product_id', $product->id)
                ->firstOrFail();

            if ($batch->quantity_available < $batchQty) {
                throw new \Exception("Not enough stock available in batch {$batch->batch_number}. Available: {$batch->quantity_available}, Requested: {$batchQty}");
            }

            $batch->quantity_available -= $batchQty;
            $batch->quantity_sold += $batchQty;
            $batch->save();

            $costPricePerUnit = (float) (optional($batch->purchaseItem)->unit_price ?? 0);
            $totalCost += $batchQty * $costPricePerUnit;
            $totalDeducted += $batchQty;

            $deductions[] = [
                'batch_id' => $batch->id,
                'quantity' => $batchQty,
                'cost_price_per_unit' => $costPricePerUnit,
            ];

            $this->batchService->logTransaction($batch, 'sold', -$batchQty, Sale::find($saleId));
        }

        if ($totalDeducted < $quantityToSell - 0.0001) {
            throw new \Exception("Total quantity from selected batches ({$totalDeducted}) is less than required quantity ({$quantityToSell})");
        }

        $averageCostPrice = $totalDeducted > 0 ? $totalCost / $totalDeducted : 0;

        return [
            'cost_price' => $averageCostPrice,
            'deductions' => $deductions,
        ];
    }

    /**
     * Whether to auto-pick batches (FEFO) vs manual batch selection.
     */
    protected function usesAutomaticBatchAllocation(array $itemData): bool
    {
        if (isset($itemData['use_fefo'])) {
            return (string) $itemData['use_fefo'] === '1';
        }

        // Legacy form field name (same checkbox, now FEFO behaviour)
        if (isset($itemData['use_fifo'])) {
            return (string) $itemData['use_fifo'] === '1';
        }

        return true;
    }

    /**
     * Deduct stock from batches (FEFO) and calculate COGS.
     *
     * @param Product $product
     * @param float $quantityToSell
     * @param int $saleId
     * @return array ['cost_price' => float, 'deductions' => array]
     * @throws InsufficientStockException
     */
    protected function deductStockAndCalculateCogs(Product $product, float $quantityToSell, int $saleId): array
    {
        $batches = ProductBatch::where('product_id', $product->id)
            ->where('quantity_available', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingToSell = $quantityToSell;
        $totalCost = 0;
        $deductions = [];

        foreach ($batches as $batch) {
            /** @var ProductBatch $batch */
            if ($remainingToSell <= 0) break;

            $deductQty = min($remainingToSell, $batch->quantity_available);

            $batch->quantity_available -= $deductQty;
            $batch->quantity_sold += $deductQty;
            $batch->save();

            // Use originating purchase item cost when available; fallback to zero if unavailable.
            $costPricePerUnit = (float) (optional($batch->purchaseItem)->unit_price ?? 0);
            $totalCost += $deductQty * $costPricePerUnit;
            $remainingToSell -= $deductQty;

            $deductions[] = [
                'batch_id' => $batch->id,
                'quantity' => $deductQty,
                'cost_price_per_unit' => $costPricePerUnit,
            ];

            // Log the transaction
            $this->batchService->logTransaction($batch, 'sold', -$deductQty, Sale::find($saleId));
        }

        if ($remainingToSell > 0) {
            throw new InsufficientStockException($product, "Insufficient batch stock for product {$product->name}.");
        }

        $averageCostPrice = $quantityToSell > 0 ? $totalCost / $quantityToSell : 0;

        return [
            'cost_price' => $averageCostPrice,
            'deductions' => $deductions,
        ];
    }

    /**
     * Update an existing sale.
     */
    public function updateSale(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            // Revert stock and COGS for existing sale items
            foreach ($sale->items as $oldItem) {
                foreach ($oldItem->batches as $saleItemBatch) {
                    /** @var ProductBatch $batch */
                    $batch = ProductBatch::find($saleItemBatch->product_batch_id);
                    if ($batch) {
                        $batch->quantity_available += $saleItemBatch->quantity;
                        $batch->quantity_sold -= $saleItemBatch->quantity;
                        $batch->save();
                        $this->batchService->logTransaction($batch, 'sale_revert', $saleItemBatch->quantity, $sale);
                    }
                }
                $oldItem->batches()->delete();
            }

            $sale->items()->delete();

            $discountType = $data['discount_type'] ?? 'fixed';
            $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0), $discountType);

            $sale->update([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? $sale->user_id,
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? null,
                'store_category' => $data['store_category'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'discount_type' => $discountType,
                'receivable_amount' => $totals['receivable'],
            ]);

            foreach ($data['items'] as $itemIndex => $itemData) {
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->firstOrFail();
                $quantityToSell = (float)$itemData['quantity'];
                
                if ($this->usesAutomaticBatchAllocation($itemData)) {
                    $cogsResult = $this->deductStockAndCalculateCogs(
                        $product,
                        $quantityToSell,
                        $sale->id
                    );
                } else {
                    $cogsResult = $this->deductFromSelectedBatches(
                        $product,
                        $itemData['batches'] ?? [],
                        $quantityToSell,
                        $sale->id
                    );
                }

                $lineDiscountRate = max((float) ($itemData['discount_rate'] ?? 0), 0);
                $lineDiscountType = $itemData['discount_type'] ?? 'fixed';
                $lineSubTotal = round($quantityToSell * (float)$itemData['unit_price'], 2);
                if ($lineDiscountType === 'percentage') {
                    $lineDiscountRate = min($lineDiscountRate, 100);
                    $lineDiscountAmount = round($lineSubTotal * $lineDiscountRate / 100, 2);
                } else {
                    $lineDiscountAmount = round($lineDiscountRate, 2);
                }
                $lineTotal = round(max(0, $lineSubTotal - $lineDiscountAmount), 2);

                $useFefo = $this->usesAutomaticBatchAllocation($itemData);
                $saleItem = SaleItem::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantityToSell,
                    'unit_label' => $product->unit->short_name ?? $product->unit->name,
                    'unit_price' => $itemData['unit_price'],
                    'discount_rate' => $lineDiscountRate,
                    'discount_type' => $lineDiscountType,
                    'line_total' => $lineTotal,
                    'cost_price' => $cogsResult['cost_price'],
                    'sale_id' => $sale->id,
                    'use_fefo' => $useFefo,
                ]);

                foreach ($cogsResult['deductions'] as $deduction) {
                    SaleItemBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'product_batch_id' => $deduction['batch_id'],
                        'quantity' => $deduction['quantity'],
                        'cost_price' => $deduction['cost_price_per_unit'],
                    ]);
                }
            }

            return $sale;
        });
    }

    /**
     * Store a payment for a sale.
     */
    public function receivePayment(Sale $sale, float $amount, ?int $userId = null): SalePayment
    {
        $payment = DB::transaction(function () use ($sale, $amount, $userId) {
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => $amount,
            ]);

            $sale->increment('paid_amount', $amount);

            return $payment;
        });

        event(new SalePaymentReceived($payment));

        return $payment;
    }

    /**
     * @deprecated Use SaleReturnService::createReturn() instead.
     */
    public function storeReturn(Sale $sale, array $data): SaleReturn
    {
        return app(SaleReturnService::class)->createReturn($sale, is_array($data) && isset($data['items']) ? $data : [
            'items' => $data,
            'discount' => 0,
            'restocking_fee' => 0,
        ]);
    }

    /**
     * Calculate subtotal, discount, and receivable for sale items.
     */
    public function calculateTotals(array $items, float $discount, string $discountType = 'fixed'): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $product = Product::with('unit')->findOrFail($row['product_id']);
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $lineSubTotal = round($qty * $price, 2);
            $lineDiscountRate = max((float) ($row['discount_rate'] ?? 0), 0);
            $lineDiscountType = $row['discount_type'] ?? 'fixed';
            $lineUnitDiscount = max((float) ($row['unit_discount'] ?? 0), 0);
            $lineUnitDiscountType = $row['unit_discount_type'] ?? 'fixed';
            if ($lineUnitDiscountType === 'percentage') {
                $lineUnitDiscount = min($lineUnitDiscount, 100);
                $lineUnitDiscountAmount = round($lineSubTotal * $lineUnitDiscount / 100, 2);
            } else {
                $lineUnitDiscountAmount = round($qty * $lineUnitDiscount, 2);
            }
            if ($lineDiscountType === 'percentage') {
                $lineDiscountRate = min($lineDiscountRate, 100);
                $lineDiscountAmount = round($lineSubTotal * $lineDiscountRate / 100, 2);
            } else {
                $lineDiscountAmount = round($lineDiscountRate, 2);
            }
            $lineTotal = round(max(0, $lineSubTotal - $lineUnitDiscountAmount - $lineDiscountAmount), 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $qty,
                'unit_label' => $product->unit->short_name ?? $product->unit->name,
                'unit_price' => $price,
                'unit_discount' => $lineUnitDiscount,
                'unit_discount_type' => $lineUnitDiscountType,
                'discount_rate' => $lineDiscountRate,
                'discount_type' => $lineDiscountType,
                'line_total' => $lineTotal,
            ];
        }

        $discount = max(0, $discount);
        if ($discountType === 'percentage') {
            $discount = min($discount, 100);
            $discountAmount = round($subtotal * $discount / 100, 2);
        } else {
            $discountAmount = round($discount, 2);
        }
        $receivable = round(max(0, $subtotal - $discountAmount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discountAmount,
            'discount_type' => $discountType,
            'receivable' => $receivable,
            'lines' => $lines,
        ];
    }
}
