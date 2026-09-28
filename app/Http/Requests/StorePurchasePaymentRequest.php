<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paying_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,check'],
            'check_number' => ['nullable', 'required_if:payment_method,check', 'string', 'max:255'],
            'check_date' => ['nullable', 'required_if:payment_method,check', 'date'],
        ];
    }
}
