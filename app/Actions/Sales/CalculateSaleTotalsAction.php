<?php

namespace App\Actions\Sales;

use App\Models\Product;

class CalculateSaleTotalsAction
{
    /**
     * Calculate subtotal, discount, and receivable for sale items.
     */
    public function execute(array $items, float $discount, string $discountType = 'fixed'): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $product = Product::with('unit')->findOrFail($row['product_id']);
            $qty = (float) $row['quantity'];
            $isFree = !empty($row['is_free']);
            $price = $isFree ? 0.00 : (float) $row['unit_price'];
            $lineSubTotal = round($qty * $price, 2);
            $lineDiscountRate = $isFree ? 0 : max((float) ($row['discount_rate'] ?? 0), 0);
            $lineDiscountType = $row['discount_type'] ?? 'fixed';
            $lineUnitDiscount = $isFree ? 0 : max((float) ($row['unit_discount'] ?? 0), 0);
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
            
            $lineTotal = $isFree ? 0.00 : round(max(0, $lineSubTotal - $lineUnitDiscountAmount - $lineDiscountAmount), 2);
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
                'is_free' => $isFree,
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
