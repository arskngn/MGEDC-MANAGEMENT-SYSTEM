<?php

namespace App\Services;

use App\Events\SaleReturnCreated;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SaleReturnItemBatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleReturnService
{
    public function __construct(protected BatchManagementService $batchService) {}

    public function createReturn(Sale $sale, array $data): SaleReturn
    {
        $saleReturn = DB::transaction(function () use ($sale, $data) {
            $this->validateReturnItems($sale, $data['items'] ?? []);

            $totals = $this->calculateTotals($sale, $data['items'] ?? [], (float) ($data['discount'] ?? 0), (float) ($data['restocking_fee'] ?? 0));

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_invoice_no' => SaleReturn::nextReturnNo(),
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'restocking_fee' => $totals['restocking_fee'],
                'payable_amount' => $totals['payable'],
                'paid_amount' => 0,
                'note' => $data['note'] ?? null,
                'status' => $data['status'] ?? (auth()->user()?->hasRole('salesperson') ? 'pending' : 'confirmed'),
            ]);

            foreach ($totals['lines'] as $line) {
                $returnItem = SaleReturnItem::create(array_merge($line, [
                    'sale_return_id' => $saleReturn->id,
                ]));

                $itemRow = collect($data['items'])->firstWhere('sale_item_id', $line['sale_item_id']);
                $this->processReturnItemBatches($returnItem, $itemRow, $line['sale_item_id'], $saleReturn);
            }

            return $saleReturn;
        });

        event(new SaleReturnCreated($saleReturn));

        return $saleReturn;
    }

    public function updateReturn(SaleReturn $saleReturn, array $data): SaleReturn
    {
        $sale = $saleReturn->sale;

        return DB::transaction(function () use ($saleReturn, $sale, $data) {
            $this->validateReturnItems($sale, $data['items'] ?? [], $saleReturn->id);

            // Revert previous return before updating
            foreach ($saleReturn->items as $oldItem) {
                foreach ($oldItem->batches as $oldBatch) {
                    $batch = $oldBatch->productBatch;
                    $this->revertReturnFromBatch($batch, (float)$oldBatch->quantity, $oldBatch->condition, $saleReturn);
                }
            }

            $saleReturn->items()->delete();

            $totals = $this->calculateTotals($sale, $data['items'] ?? [], (float) ($data['discount'] ?? 0), (float) ($data['restocking_fee'] ?? 0), $saleReturn->id);

            if ($totals['payable'] < (float) $saleReturn->paid_amount - 0.0001) {
                throw new InvalidArgumentException('Payable cannot be less than amount already paid to customer.');
            }

            $saleReturn->update([
                'return_date' => $data['return_date'] ?? $saleReturn->return_date,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'restocking_fee' => $totals['restocking_fee'],
                'payable_amount' => $totals['payable'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($totals['lines'] as $line) {
                $returnItem = SaleReturnItem::create(array_merge($line, [
                    'sale_return_id' => $saleReturn->id,
                ]));

                $itemRow = collect($data['items'])->firstWhere('sale_item_id', $line['sale_item_id']);
                $this->processReturnItemBatches($returnItem, $itemRow, $line['sale_item_id'], $saleReturn, $saleReturn->id);
            }

            return $saleReturn;
        });
    }

    public function rejectReturn(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn) {
            $saleReturn->load(['items.batches.productBatch']);

            foreach ($saleReturn->items as $item) {
                foreach ($item->batches as $returnBatch) {
                    $batch = $returnBatch->productBatch;
                    if ($batch) {
                        $this->revertReturnFromBatch(
                            $batch,
                            (float) $returnBatch->quantity,
                            $returnBatch->condition,
                            $saleReturn
                        );
                    }
                }
            }

            $saleReturn->update(['status' => 'rejected']);
        });
    }

    protected function processReturnItemBatches(
        SaleReturnItem $returnItem,
        array $itemRow,
        int $saleItemId,
        SaleReturn $saleReturn,
        ?int $excludeReturnId = null
    ): void {
        $useFefo = (string) ($itemRow['use_fefo'] ?? '1') === '1';

        if ($useFefo) {
            $this->allocateFefoReturns(
                $returnItem,
                (float) ($itemRow['good_quantity'] ?? 0),
                (float) ($itemRow['damaged_quantity'] ?? 0),
                $saleItemId,
                $saleReturn,
                $excludeReturnId
            );

            return;
        }

        foreach ($itemRow['batches'] ?? [] as $batchRow) {
            $batchId = (int) ($batchRow['batch_id'] ?? 0);
            if (! $batchId) {
                continue;
            }

            foreach ([
                SaleReturnItemBatch::CONDITION_GOOD => (float) ($batchRow['good_quantity'] ?? 0),
                SaleReturnItemBatch::CONDITION_DAMAGED => (float) ($batchRow['damaged_quantity'] ?? 0),
            ] as $condition => $quantity) {
                if ($quantity > 0) {
                    $this->createReturnBatchRecord($returnItem, $batchId, $quantity, $condition, $saleReturn);
                }
            }
        }
    }

    protected function allocateFefoReturns(
        SaleReturnItem $returnItem,
        float $goodQty,
        float $damagedQty,
        int $saleItemId,
        SaleReturn $saleReturn,
        ?int $excludeReturnId = null
    ): void {
        $allocations = $this->getOrderedAllocations($saleItemId);

        foreach ([
            SaleReturnItemBatch::CONDITION_GOOD => $goodQty,
            SaleReturnItemBatch::CONDITION_DAMAGED => $damagedQty,
        ] as $condition => $remainingToAllocate) {
            foreach ($allocations as $allocation) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $remainingForBatch = SaleReturn::remainingReturnableForSaleItemBatch($allocation, $excludeReturnId);
                $allocateQty = min($remainingToAllocate, $remainingForBatch);

                if ($allocateQty > 0) {
                    $this->createReturnBatchRecord(
                        $returnItem,
                        $allocation->product_batch_id,
                        $allocateQty,
                        $condition,
                        $saleReturn
                    );

                    $remainingToAllocate -= $allocateQty;
                }
            }
        }
    }

    protected function createReturnBatchRecord(
        SaleReturnItem $returnItem,
        int $batchId,
        float $quantity,
        string $condition,
        SaleReturn $saleReturn
    ): void {
        $condition = $this->normalizeCondition($condition);

        SaleReturnItemBatch::create([
            'sale_return_item_id' => $returnItem->id,
            'product_batch_id' => $batchId,
            'quantity' => $quantity,
            'condition' => $condition,
        ]);

        $batch = ProductBatch::findOrFail($batchId);
        $this->applyReturnToBatch($batch, $quantity, $condition, $saleReturn);
    }

    protected function getLineReturnQuantity(array $item): float
    {
        $useFefo = (string) ($item['use_fefo'] ?? '1') === '1';

        if ($useFefo || empty($item['batches'])) {
            return (float) ($item['good_quantity'] ?? 0) + (float) ($item['damaged_quantity'] ?? 0);
        }

        return collect($item['batches'] ?? [])->sum(fn ($batch) => $this->getBatchReturnQuantity($batch));
    }

    protected function getBatchReturnQuantity(array $batchRow): float
    {
        return (float) ($batchRow['good_quantity'] ?? 0) + (float) ($batchRow['damaged_quantity'] ?? 0);
    }

    protected function getOrderedAllocations(int $saleItemId): \Illuminate\Support\Collection
    {
        // Get the sale item batches in the order they were created (which is FEFO order as per how sale was created)
        return SaleItemBatch::query()
            ->where('sale_item_id', $saleItemId)
            ->orderBy('id') // The order they were added to the sale item is FEFO order
            ->get();
    }

    protected function validateReturnItems(Sale $sale, array $items, ?int $excludeReturnId = null): void
    {
        $hasPositive = false;

        foreach ($items as $item) {
            $saleItem = $sale->items->firstWhere('id', $item['sale_item_id'] ?? null);
            if (! $saleItem) {
                continue;
            }

            $useFefo = (string) ($item['use_fefo'] ?? '1') === '1';

            if ($useFefo) {
                $returnQty = $this->getLineReturnQuantity($item);

                if ($returnQty <= 0) {
                    continue;
                }

                $hasPositive = true;

                $remainingLine = SaleReturn::remainingReturnableForSaleItem($saleItem, $excludeReturnId);
                if ($returnQty > $remainingLine + 0.0001) {
                    throw new InvalidArgumentException(
                        "Return quantity for {$saleItem->product_name} cannot exceed what is still returnable on this sale ({$remainingLine})."
                    );
                }
            } else {
                $rowTotal = 0;
                foreach ($item['batches'] ?? [] as $batchRow) {
                    $batchRq = $this->getBatchReturnQuantity($batchRow);
                    $rowTotal += $batchRq;

                    if ($batchRq > 0) {
                        $hasPositive = true;
                    }
                }

                if ($rowTotal <= 0) {
                    continue;
                }

                $remainingLine = SaleReturn::remainingReturnableForSaleItem($saleItem, $excludeReturnId);
                if ($rowTotal > $remainingLine + 0.0001) {
                    throw new InvalidArgumentException(
                        "Return quantity for {$saleItem->product_name} cannot exceed what is still returnable on this sale ({$remainingLine})."
                    );
                }
            }
        }

        if (! $hasPositive) {
            throw new InvalidArgumentException('Enter a return quantity for at least one item.');
        }
    }

    protected function applyReturnToBatch(ProductBatch $batch, float $quantity, string $condition, SaleReturn $saleReturn): void
    {
        // For damaged returns, don't modify the batch at all
        if ($condition === SaleReturnItemBatch::CONDITION_DAMAGED) {
            // Just log the transaction without modifying batch quantities
            $this->batchService->logTransaction($batch, 'sale_return_damaged', $quantity, $saleReturn);
            return;
        }

        // Only modify batches for good condition returns
        $batch->quantity_sold = max(0, (float) $batch->quantity_sold - $quantity);
        $batch->quantity_available += $quantity;
        $batch->quantity_returned += $quantity;

        if ($batch->quantity_available > 0 && in_array($batch->status, [ProductBatch::STATUS_DEPLETED, 'sold'], true)) {
            $batch->status = ProductBatch::STATUS_ACTIVE;
        }

        $this->batchService->logTransaction($batch, 'sale_return', $quantity, $saleReturn);

        $batch->save();
    }

    protected function revertReturnFromBatch(ProductBatch $batch, float $quantity, string $condition, SaleReturn $saleReturn): void
    {
        // For damaged returns, nothing to revert (we didn't modify the batch in the first place)
        if ($condition === SaleReturnItemBatch::CONDITION_DAMAGED) {
            $this->batchService->logTransaction($batch, 'sale_return_damaged_revert', -$quantity, $saleReturn);
            return;
        }

        // Only revert changes for good condition returns
        $batch->quantity_sold = (float) $batch->quantity_sold + $quantity;
        $batch->quantity_available -= $quantity;
        $batch->quantity_returned -= $quantity;

        // Update status if needed
        if ((float) $batch->quantity_available <= 0) {
            if ((float) $batch->quantity_sold > 0) {
                $batch->status = 'sold';
            } else {
                $batch->status = ProductBatch::STATUS_DEPLETED;
            }
        }

        $this->batchService->logTransaction($batch, 'sale_return_revert', -$quantity, $saleReturn);

        $batch->save();
    }

    protected function normalizeCondition(string $condition): string
    {
        $condition = strtolower(trim($condition));

        return in_array($condition, [SaleReturnItemBatch::CONDITION_GOOD, SaleReturnItemBatch::CONDITION_DAMAGED], true)
            ? $condition
            : SaleReturnItemBatch::CONDITION_GOOD;
    }

    /**
     * @return array{subtotal: float, discount: float, restocking_fee: float, payable: float, lines: array<int, array<string, mixed>>}
     */
    protected function calculateTotals(Sale $sale, array $items, float $discount, float $restockingFee, ?int $excludeReturnId = null): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $item) {
            $saleItem = $sale->items->firstWhere('id', $item['sale_item_id'] ?? null);
            if (! $saleItem) {
                continue;
            }

            $useFefo = (string) ($item['use_fefo'] ?? '1') === '1';
            $returnQty = $this->getLineReturnQuantity($item);

            if ($returnQty <= 0) {
                continue;
            }

            $lineTotal = round($returnQty * (float) $saleItem->unit_price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->product_id,
                'product_name' => $saleItem->product_name,
                'sku' => $saleItem->sku,
                'sale_quantity' => $saleItem->quantity,
                'return_quantity' => $returnQty,
                'unit_label' => $saleItem->unit_label,
                'unit_price' => $saleItem->unit_price,
                'cost_price' => $saleItem->cost_price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round($discount, 2);
        $restockingFee = round($restockingFee, 2);
        $payable = round(max(0, $subtotal - $discount - $restockingFee), 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'restocking_fee' => $restockingFee,
            'payable' => $payable,
            'lines' => $lines,
        ];
    }
}
