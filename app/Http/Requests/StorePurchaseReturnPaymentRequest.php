<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiving_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,check'],
            'check_number' => ['required_if:payment_method,check', 'nullable', 'string', 'max:255'],
            'check_date' => ['required_if:payment_method,check', 'nullable', 'date'],
        ];
    }
}
