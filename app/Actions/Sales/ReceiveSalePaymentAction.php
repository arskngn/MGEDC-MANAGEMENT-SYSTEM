<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Events\SalePaymentReceived;
use Illuminate\Support\Facades\DB;

class ReceiveSalePaymentAction
{
    /**
     * Store a payment for a sale.
     */
    public function execute(Sale $sale, float $amount, ?int $userId = null, string $paymentMethod = 'cash', ?string $checkNumber = null, ?string $checkDate = null): SalePayment
    {
        $payment = DB::transaction(function () use ($sale, $amount, $userId, $paymentMethod, $checkNumber, $checkDate) {
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'check_number' => $checkNumber,
                'check_date' => $checkDate,
            ]);

            $sale->increment('paid_amount', $amount);

            return $payment;
        });

        event(new SalePaymentReceived($payment));

        return $payment;
    }
}
