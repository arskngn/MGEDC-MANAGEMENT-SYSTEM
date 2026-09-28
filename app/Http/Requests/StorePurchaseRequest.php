<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', 'string', 'max:64', 'unique:purchases,invoice_no'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.unit_discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_discount_type' => ['nullable', 'in:fixed,percentage'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:fixed,percentage'],
        ];
    }
}
