<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnItemBatch;
use App\Models\PurchaseReturnPayment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Events\PurchaseReturnCreated;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use App\Services\BatchManagementService;

class PurchaseReturnService
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }

    /**
     * Store a new purchase return.
     */
    public function createReturn(Purchase $purchase, array $data): PurchaseReturn
    {
        $ret = DB::transaction(function () use ($purchase, $data) {
            // Validate batches and stock before recording return
            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items->firstWhere('id', $item['purchase_item_id']);
                if (!$purchaseItem) continue;

                $product = Product::where('id', $purchaseItem->product_id)->lockForUpdate()->firstOrFail();
                
                $requiresBatchTracking = (bool) $product->requires_batch_tracking;
                
                $totalReturnQty = 0;
                
                if ($requiresBatchTracking) {
                    $itemBatches = $item['batches'] ?? [];
                    $totalReturnQty = collect($itemBatches)
                        ->filter(fn ($batch) => ! empty($batch['batch_id']))
                        ->sum(fn ($batch) => (float) ($batch['quantity'] ?? 0));

                    if ($totalReturnQty > 0) {
                        // 1. Check total return quantity against remaining returnable
                        $remaining = PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem);
                        if ($totalReturnQty > $remaining) {
                            throw new InsufficientStockException($product, "Returning more than what was originally purchased or already returned.");
                        }

                        // 2. Validate each batch
                        foreach ($itemBatches as $batchData) {
                            if (empty($batchData['batch_id']) || empty($batchData['quantity'])) continue;
                            
                            $batch = ProductBatch::where('id', $batchData['batch_id'])
                                ->where('product_id', $product->id)
                                ->where('purchase_id', $purchase->id) // Only batches from this purchase
                                ->lockForUpdate()
                                ->firstOrFail();
                            
                            $batchQty = (float)$batchData['quantity'];
                            
                            if ($batch->quantity_available < $batchQty) {
                                throw new InsufficientStockException($product, "Insufficient stock in batch {$batch->batch_number} (available: {$batch->quantity_available}).");
                            }
                        }
                    }
                } else {
                    // Non-batch product - use return_quantity directly
                    $totalReturnQty = (float) ($item['return_quantity'] ?? 0);
                    
                    if ($totalReturnQty > 0) {
                        // Check total return quantity against remaining returnable
                        $remaining = PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem);
                        if ($totalReturnQty > $remaining) {
                            throw new InsufficientStockException($product, "Returning more than what was originally purchased or already returned.");
                        }
                    }
                }
            }

            $totals = $this->calculateTotals($purchase, $data['items'], (float) ($data['discount'] ?? 0));

            $ret = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_invoice_no' => PurchaseReturn::nextInvoiceNo(),
                'return_date' => $data['return_date'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
                'received_amount' => 0,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($totals['lines'] as $line) {
                $returnItem = PurchaseReturnItem::create(array_merge($line, [
                    'purchase_return_id' => $ret->id,
                ]));

                $itemRow = collect($data['items'])->firstWhere('purchase_item_id', $line['purchase_item_id']);
                $product = Product::find($purchase->items->firstWhere('id', $line['purchase_item_id'])->product_id);
                $requiresBatchTracking = (bool) $product->requires_batch_tracking;
                
                if ($requiresBatchTracking) {
                    $itemBatches = $itemRow['batches'] ?? [];
                    
                    foreach ($itemBatches as $batchData) {
                        if (empty($batchData['batch_id']) || empty($batchData['quantity'])) continue;
                        
                        $batch = ProductBatch::findOrFail($batchData['batch_id']);
                        $batchQty = (float)$batchData['quantity'];
                        
                        // Create the batch relationship
                        PurchaseReturnItemBatch::create([
                            'purchase_return_item_id' => $returnItem->id,
                            'product_batch_id' => $batch->id,
                            'quantity' => $batchQty,
                        ]);
                        
                        // Update the batch stock
                        $this->deductFromBatchForPurchaseReturn($batch, $batchQty, $ret);
                    }
                } else {
                    $qty = (float) ($itemRow['return_quantity'] ?? 0);
                    if ($qty > 0) {
                        $batchNumber = 'DEFAULT-' . $product->id . '-' . $purchase->id;
                        $batch = ProductBatch::where([
                            'product_id'   => $product->id,
                            'batch_number' => $batchNumber,
                        ])->first();

                        if ($batch) {
                            PurchaseReturnItemBatch::create([
                                'purchase_return_item_id' => $returnItem->id,
                                'product_batch_id' => $batch->id,
                                'quantity' => $qty,
                            ]);
                            $this->deductFromBatchForPurchaseReturn($batch, $qty, $ret);
                        }
                    }
                }
            }

            return $ret;
        });

        event(new PurchaseReturnCreated($ret));

        return $ret;
    }

    protected function deductFromBatchForPurchaseReturn(ProductBatch $batch, float $quantity, PurchaseReturn $return): void
    {
        $batch->quantity_available -= $quantity;
        $batch->quantity_adjusted += $quantity; 
        $batch->save();

        $this->batchService->logTransaction($batch, 'purchase_return', -$quantity, $return);
    }

    protected function revertBatchDeductionForPurchaseReturn(ProductBatch $batch, float $quantity, PurchaseReturn $return): void
    {
        $batch->quantity_available += $quantity;
        $batch->quantity_adjusted -= $quantity;
        $batch->save();

        $this->batchService->logTransaction($batch, 'purchase_return_revert', $quantity, $return);
    }

    /**
     * Update an existing purchase return.
     */
    public function updateReturn(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
    {
        $purchase = $purchaseReturn->purchase;

        return DB::transaction(function () use ($purchaseReturn, $purchase, $data) {
            // Validate batches and stock before updating
            // First, collect what THIS return currently has deducted per batch
            $existingBatchQtys = [];
            foreach ($purchaseReturn->items as $existingItem) {
                foreach ($existingItem->batches as $existingBatch) {
                    $existingBatchQtys[$existingBatch->product_batch_id] = 
                        ($existingBatchQtys[$existingBatch->product_batch_id] ?? 0) + (float) $existingBatch->quantity;
                }
            }

            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items->firstWhere('id', $item['purchase_item_id']);
                if (!$purchaseItem) continue;

                $product = Product::where('id', $purchaseItem->product_id)->lockForUpdate()->firstOrFail();
                
                $requiresBatchTracking = (bool) $product->requires_batch_tracking;
                
                $totalReturnQty = 0;
                
                if ($requiresBatchTracking) {
                    $itemBatches = $item['batches'] ?? [];
                    $totalReturnQty = collect($itemBatches)
                        ->filter(fn ($batch) => ! empty($batch['batch_id']))
                        ->sum(fn ($batch) => (float) ($batch['quantity'] ?? 0));

                    if ($totalReturnQty > 0) {
                        // 1. Check remaining returnable (accounting for what's already returned in THIS transaction)
                        $remaining = PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem, $purchaseReturn->id);
                        if ($totalReturnQty > $remaining) {
                            throw new InsufficientStockException($product, "Returning more than what was originally purchased or already returned.");
                        }

                        // 2. Validate each batch (add back what this return already deducted)
                        foreach ($itemBatches as $batchData) {
                            if (empty($batchData['batch_id']) || empty($batchData['quantity'])) continue;
                            
                            $batch = ProductBatch::where('id', $batchData['batch_id'])
                                ->where('product_id', $product->id)
                                ->where('purchase_id', $purchase->id)
                                ->lockForUpdate()
                                ->firstOrFail();
                            
                            $batchQty = (float)$batchData['quantity'];
                            $addBack = $existingBatchQtys[$batch->id] ?? 0;
                            $effectiveAvailable = $batch->quantity_available + $addBack;
                            
                            if ($effectiveAvailable < $batchQty) {
                                throw new InsufficientStockException($product, "Insufficient stock in batch {$batch->batch_number}.");
                            }
                        }
                    }
                } else {
                    // Non-batch product - use return_quantity directly
                    $totalReturnQty = (float) ($item['return_quantity'] ?? 0);
                    
                    if ($totalReturnQty > 0) {
                        // Check remaining returnable (accounting for what's already returned in THIS transaction)
                        $remaining = PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem, $purchaseReturn->id);
                        if ($totalReturnQty > $remaining) {
                            throw new InsufficientStockException($product, "Returning more than what was originally purchased or already returned.");
                        }
                    }
                }
            }

            $totals = $this->calculateTotals($purchase, $data['items'], (float) ($data['discount'] ?? 0), $purchaseReturn->id);

            // Revert previous batch deductions before deleting items
            foreach ($purchaseReturn->items as $oldItem) {
                foreach ($oldItem->batches as $oldBatch) {
                    $batch = $oldBatch->productBatch;
                    $this->revertBatchDeductionForPurchaseReturn($batch, (float)$oldBatch->quantity, $purchaseReturn);
                }
            }

            $purchaseReturn->items()->delete();

            $purchaseReturn->update([
                'return_date' => $data['return_date'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($totals['lines'] as $line) {
                $returnItem = PurchaseReturnItem::create(array_merge($line, [
                    'purchase_return_id' => $purchaseReturn->id,
                ]));

                $itemRow = collect($data['items'])->firstWhere('purchase_item_id', $line['purchase_item_id']);
                $product = Product::find($purchase->items->firstWhere('id', $line['purchase_item_id'])->product_id);
                $requiresBatchTracking = (bool) $product->requires_batch_tracking;
                
                if ($requiresBatchTracking) {
                    $itemBatches = $itemRow['batches'] ?? [];
                    
                    foreach ($itemBatches as $batchData) {
                        if (empty($batchData['batch_id']) || empty($batchData['quantity'])) continue;
                        
                        $batch = ProductBatch::findOrFail($batchData['batch_id']);
                        $batchQty = (float)$batchData['quantity'];
                        
                        // Create the batch relationship
                        PurchaseReturnItemBatch::create([
                            'purchase_return_item_id' => $returnItem->id,
                            'product_batch_id' => $batch->id,
                            'quantity' => $batchQty,
                        ]);
                        
                        // Update the batch stock
                        $this->deductFromBatchForPurchaseReturn($batch, $batchQty, $purchaseReturn);
                    }
                } else {
                    $qty = (float) ($itemRow['return_quantity'] ?? 0);
                    if ($qty > 0) {
                        $batchNumber = 'DEFAULT-' . $product->id . '-' . $purchase->id;
                        $batch = ProductBatch::where([
                            'product_id'   => $product->id,
                            'batch_number' => $batchNumber,
                        ])->first();

                        if ($batch) {
                            PurchaseReturnItemBatch::create([
                                'purchase_return_item_id' => $returnItem->id,
                                'product_batch_id' => $batch->id,
                                'quantity' => $qty,
                            ]);
                            $this->deductFromBatchForPurchaseReturn($batch, $qty, $purchaseReturn);
                        }
                    }
                }
            }

            return $purchaseReturn;
        });
    }

    /**
     * Record a payment for a purchase return.
     */
    public function recordPayment(PurchaseReturn $purchaseReturn, float $amount, ?int $userId = null, string $paymentMethod = 'cash', ?string $checkNumber = null, ?string $checkDate = null): PurchaseReturnPayment
    {
        return DB::transaction(function () use ($purchaseReturn, $amount, $userId, $paymentMethod, $checkNumber, $checkDate) {
            $payment = PurchaseReturnPayment::create([
                'purchase_return_id' => $purchaseReturn->id,
                'user_id' => $userId,
                'amount' => $amount,
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'check_number' => $checkNumber,
                'check_date' => $checkDate,
            ]);

            $purchaseReturn->increment('received_amount', $amount);

            return $payment;
        });
    }

    /**
     * Calculate subtotal, discount, and receivable for purchase return items.
     */
    public function calculateTotals(Purchase $purchase, array $items, float $discount, ?int $excludeReturnId = null): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $purchaseItem = $purchase->items->firstWhere('id', $row['purchase_item_id']);
            if (!$purchaseItem) continue;

            $product = Product::find($purchaseItem->product_id);
            $requiresBatchTracking = (bool) $product->requires_batch_tracking;
            
            $qty = 0;
            
            if ($requiresBatchTracking) {
                $itemBatches = $row['batches'] ?? [];
                $qty = collect($itemBatches)
                    ->filter(fn ($batch) => ! empty($batch['batch_id']))
                    ->sum(fn ($batch) => (float) ($batch['quantity'] ?? 0));
            } else {
                $qty = (float) ($row['return_quantity'] ?? 0);
            }
            
            if ($qty <= 0) {
                continue;
            }

            $price = (float) $purchaseItem->unit_price;
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'purchase_item_id' => $purchaseItem->id,
                'product_id' => $purchaseItem->product_id,
                'product_name' => $purchaseItem->product_name,
                'sku' => $purchaseItem->sku,
                'purchase_quantity' => (float) $purchaseItem->quantity,
                'stock_quantity' => PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem, $excludeReturnId),
                'return_quantity' => $qty,
                'unit_label' => $purchaseItem->unit_label,
                'unit_price' => $price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round($discount, 2);
        $receivable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'receivable' => $receivable,
            'lines' => $lines,
        ];
    }
}
