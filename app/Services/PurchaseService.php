<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Events\PurchaseCreated;
use App\Events\PurchasePaymentRecorded;
use Illuminate\Support\Facades\DB;
use App\Services\BatchManagementService;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }

    /**
     * Store a new purchase.
     */
    public function createPurchase(array $data): Purchase
    {
        $discountType = $data['discount_type'] ?? 'fixed';
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0), $discountType, (float) ($data['tax_rate'] ?? 0));

        $purchase = DB::transaction(function () use ($data, $totals, $discountType) {
            $purchase = Purchase::create([
                'invoice_no'             => $data['invoice_no'],
                'supplier_id'            => $data['supplier_id'],
                'purchase_date'          => $data['purchase_date'],
                'delivery_date'          => $data['delivery_date'] ?? null,
                'note'                   => $data['note'] ?? null,
                'subtotal'               => $totals['subtotal'],
                'discount'               => $totals['discount'],
                'discount_type'          => $discountType,
                'tax_rate'               => $data['tax_rate'] ?? 0,
                'tax_amount'             => $totals['tax_amount'],
                'payable_amount'         => $totals['payable'],
                'paid_amount'            => 0,
                'status'                 => 'ordered',
            ]);

            foreach ($totals['lines'] as $line) {
                $purchaseItem = PurchaseItem::create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));
            }

            return $purchase;
        });

        event(new PurchaseCreated($purchase));

        return $purchase;
    }

    /**
     * Update an existing purchase.
     */
    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        $discountType = $data['discount_type'] ?? 'fixed';
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0), $discountType, (float) ($data['tax_rate'] ?? 0));

        return DB::transaction(function () use ($purchase, $data, $totals, $discountType) {
            $purchase->items()->each(function(PurchaseItem $item) {
                $item->delete();
            });

            $purchase->update([
                'invoice_no'             => $data['invoice_no'],
                'supplier_id'            => $data['supplier_id'],
                'purchase_date'          => $data['purchase_date'],
                'delivery_date'          => $data['delivery_date'] ?? null,
                'note'                   => $data['note'] ?? null,
                'subtotal'               => $totals['subtotal'],
                'discount'               => $totals['discount'],
                'discount_type'          => $discountType,
                'tax_rate'               => $data['tax_rate'] ?? 0,
                'tax_amount'             => $totals['tax_amount'],
                'payable_amount'         => $totals['payable'],
                'status'                 => $purchase->status,
            ]);

            foreach ($totals['lines'] as $line) {
                $purchaseItem = PurchaseItem::create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));
            }

            return $purchase;
        });
    }

    /**
     * Receive items for a purchase and create batches.
     */
    public function receiveItems(Purchase $purchase, array $data): void
    {
        DB::transaction(function () use ($purchase, $data) {
            $purchase->update([
                'delivery_date' => $data['delivery_date'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                $purchaseItem = PurchaseItem::findOrFail($itemData['purchase_item_id']);
                $receiptQty = (float) $itemData['receipt_quantity'];

                if ($receiptQty <= 0) continue;

                $purchaseItem->increment('received_quantity', $receiptQty);

                if (isset($itemData['batches']) && count($itemData['batches']) > 0) {
                    foreach ($itemData['batches'] as $batchData) {
                        $this->createBatchFromReceipt($purchase, $purchaseItem, $batchData);
                    }
                } else {
                    // Fallback for non-batched items if they still need a generic batch or just stock update
                    // In this system, we mostly use batches for stock tracking.
                    $this->createBatchFromReceipt($purchase, $purchaseItem, [
                        'batch_number' => 'B-' . $purchase->invoice_no . '-' . $purchaseItem->id,
                        'quantity' => $receiptQty,
                    ]);
                }
            }

            // Update purchase status based on total received quantity
            $purchase->load('items');
            $totalOrdered = $purchase->items->sum('quantity');
            $totalReceived = $purchase->items->sum('received_quantity');

            if ($totalReceived >= $totalOrdered) {
                $purchase->update(['status' => 'received']);
            } else if ($totalReceived > 0) {
                $purchase->update(['status' => 'partial']);
            }
        });
    }

    /**
     * Create or update a product batch from a receipt.
     */
    protected function createBatchFromReceipt(Purchase $purchase, PurchaseItem $item, array $batchData): void
    {
        $product = $item->product;
        $qty = (float) $batchData['quantity'];
        $expiryDate = !empty($batchData['expiry_date']) ? $batchData['expiry_date'] : null;

        if (!$product->requires_batch_tracking) {
            // For non-batch-tracked products, use a single default batch per product
            $batchNumber = 'DEFAULT-' . $product->id . '-' . $purchase->id;
            $batch = ProductBatch::where([
                'product_id'   => $item->product_id,
                'batch_number' => $batchNumber,
            ])->first();

            if ($batch) {
                $batch->update([
                    'quantity'           => $batch->quantity + $qty,
                    'quantity_available' => $batch->quantity_available + $qty,
                    'expiry_date'        => $expiryDate ?? $batch->expiry_date,
                ]);
            } else {
                $batch = ProductBatch::create([
                    'product_id'         => $item->product_id,
                    'batch_number'       => $batchNumber,
                    'quantity'           => $qty,
                    'quantity_available' => $qty,
                    'expiry_date'        => $expiryDate,
                ]);
            }

            $item->update(['product_batch_id' => $batch->id]);
            $this->batchService->logTransaction($batch->fresh(), 'received', $qty, $purchase);
            return;
        }

        // Auto-generate batch number if not provided
        $batchNumber = !empty($batchData['batch_number'])
            ? $batchData['batch_number']
            : 'B-' . $purchase->invoice_no . '-' . $item->id . '-' . now()->format('HisuN');

        $batch = ProductBatch::where([
            'product_id'   => $item->product_id,
            'batch_number' => $batchNumber,
        ])->first();

        if ($batch) {
            $batch->update([
                'purchase_id'        => $purchase->id,
                'purchase_item_id'   => $item->id,
                'quantity'           => $batch->quantity + $qty,
                'quantity_available' => $batch->quantity_available + $qty,
                'expiry_date'        => $expiryDate ?? $batch->expiry_date,
            ]);
        } else {
            $batch = ProductBatch::create([
                'product_id'         => $item->product_id,
                'batch_number'       => $batchNumber,
                'purchase_id'        => $purchase->id,
                'purchase_item_id'   => $item->id,
                'quantity'           => $qty,
                'quantity_available' => $qty,
                'expiry_date'        => $expiryDate,
            ]);
        }

        // Log the stock transaction
        $this->batchService->logTransaction($batch->fresh(), 'received', $qty, $purchase);
    }



    /**
     * Calculate subtotal, discount, taxes and payable.
     */
    public function calculateTotals(array $items, float $discount, string $discountType = 'fixed', float $taxRate = 0): array
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
                'product_id'           => $product->id,
                'product_name'         => $product->name,
                'sku'                  => $product->sku,
                'quantity'             => $qty,
                'unit_label'           => $product->unit->short_name ?? $product->unit->name,
                'unit_price'           => $price,
                'unit_discount'        => $lineUnitDiscount,
                'unit_discount_type'   => $lineUnitDiscountType,
                'discount_rate'        => $lineDiscountRate,
                'discount_type'        => $lineDiscountType,
                'line_total'           => $lineTotal,
            ];
        }

        $discount = max($discount, 0);
        if ($discountType === 'percentage') {
            $discount = min($discount, 100);
            $discountAmount = round($subtotal * $discount / 100, 2);
        } else {
            $discountAmount = round($discount, 2);
        }
        $taxableBase = max(0, $subtotal - $discountAmount);
        $taxAmount = round($taxableBase * $taxRate / 100, 2);
        $payable = round(max(0, $taxableBase + $taxAmount), 2);

        return [
            'subtotal'           => round($subtotal, 2),
            'discount'           => $discountAmount,
            'discount_type'      => $discountType,
            'tax_amount'         => $taxAmount,
            'payable'            => $payable,
            'lines'              => $lines,
        ];
    }

    /**
     * Store a payment for a purchase.
     */
    public function recordPayment(Purchase $purchase, float $amount, ?int $userId = null, string $paymentMethod = 'cash', ?string $checkNumber = null, ?string $checkDate = null): PurchasePayment
    {
        $payment = DB::transaction(function () use ($purchase, $amount, $userId, $paymentMethod, $checkNumber, $checkDate) {
            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'check_number' => $checkNumber,
                'check_date' => $checkDate,
            ]);

            $purchase->increment('paid_amount', $amount);

            return $payment;
        });

        event(new PurchasePaymentRecorded($payment));

        return $payment;
    }
}
