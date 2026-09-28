<?php

namespace App\Http\Requests;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Models\SaleReturn;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
            'restocking_fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.use_fefo' => ['required', 'in:0,1'],
            'items.*.good_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.damaged_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.batches' => ['nullable', 'array', 'required_if:items.*.use_fefo,0'],
            'items.*.batches.*.batch_id' => ['required_with:items.*.batches', 'integer'],
            'items.*.batches.*.good_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.batches.*.damaged_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $excludeReturnId = null;
            
            /** @var Sale $sale */
            if ($this->route('sale')) {
                $sale = $this->route('sale');
            } elseif ($this->route('saleReturn')) {
                /** @var SaleReturn $saleReturn */
                $saleReturn = $this->route('saleReturn');
                $excludeReturnId = $saleReturn->id;
                $sale = $saleReturn->sale;
            }
            
            $sale->loadMissing('items.batches');

            $hasPositive = false;

            foreach ($this->input('items', []) as $i => $row) {
                $saleItem = SaleItem::find($row['sale_item_id'] ?? null);
                if (! $saleItem || $saleItem->sale_id !== $sale->id) {
                    $validator->errors()->add("items.$i.sale_item_id", 'Invalid sale line.');

                    continue;
                }

                $useFefo = (string) ($row['use_fefo'] ?? '1') === '1';

                if ($useFefo) {
                    $rq = (float) ($row['good_quantity'] ?? 0) + (float) ($row['damaged_quantity'] ?? 0);

                    if ($rq > 0) {
                        $hasPositive = true;
                    }

                    $remaining = SaleReturn::remainingReturnableForSaleItem($saleItem, $excludeReturnId);
                    if ($rq > $remaining + 0.0001) {
                        $validator->errors()->add(
                            "items.$i.good_quantity",
                            'Total return quantity cannot exceed what is still returnable on this sale line ('.$remaining.').'
                        );
                    }
                } else {
                    $rowTotal = 0;
                    foreach ($row['batches'] ?? [] as $batchIndex => $batchRow) {
                        $batchId = (int) ($batchRow['batch_id'] ?? 0);
                        $batchRq = (float) ($batchRow['good_quantity'] ?? 0) + (float) ($batchRow['damaged_quantity'] ?? 0);
                        $rowTotal += $batchRq;

                        // Find the original sale item batch
                        $saleItemBatch = SaleItemBatch::where('sale_item_id', $saleItem->id)
                            ->where('product_batch_id', $batchId)
                            ->first();

                        if (! $saleItemBatch) {
                            $validator->errors()->add("items.$i.batches.$batchIndex.batch_id", 'This batch is not part of the original sale.');
                            continue;
                        }

                        $remainingBatch = SaleReturn::remainingReturnableForSaleItemBatch($saleItemBatch, $excludeReturnId);
                        if ($batchRq > $remainingBatch + 0.0001) {
                            $validator->errors()->add(
                                "items.$i.batches.$batchIndex.good_quantity",
                                "Quantity cannot exceed what is still returnable for this batch ({$remainingBatch})."
                            );
                        }
                    }

                    if ($rowTotal > 0) {
                        $hasPositive = true;
                    }

                    // Check total for the row
                    $remainingTotal = SaleReturn::remainingReturnableForSaleItem($saleItem, $excludeReturnId);
                    if ($rowTotal > $remainingTotal + 0.0001) {
                        $validator->errors()->add(
                            "items.$i",
                            "Total quantity for all batches cannot exceed what is still returnable on this sale line ({$remainingTotal})."
                        );
                    }
                }
            }

            if (! $hasPositive) {
                $validator->errors()->add('items', 'Enter a return quantity for at least one item.');
            }
        });
    }
}
