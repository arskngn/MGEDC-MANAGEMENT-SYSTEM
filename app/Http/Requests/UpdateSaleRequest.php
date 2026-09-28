<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSaleBatchQuantities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
{
    use ValidatesSaleBatchQuantities;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_no' => [
                'required',
                'string',
                'max:64',
                Rule::unique('sales', 'invoice_no')->ignore($this->route('sale')),
            ],
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'store_category' => ['nullable', 'string'],
            'salesperson_id' => ['nullable', 'exists:users,id'],
            'discount' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.unit_discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_discount_type' => ['nullable', 'in:fixed,percentage'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:fixed,percentage'],
            'items.*.use_fefo' => ['nullable'],
            'items.*.is_free' => ['nullable', 'boolean'],
            'items.*.batches' => ['nullable', 'array'],
            'items.*.batches.*.batch_id' => ['required_with:items.*.batches', 'integer'],
            'items.*.batches.*.quantity' => ['required_with:items.*.batches', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateSaleBatchQuantities($validator);
            $sale = $this->route('sale');
            // Prevent changing these fields
            if ($this->input('invoice_no') !== $sale->invoice_no) {
                $validator->errors()->add('invoice_no', 'Invoice no cannot be changed.');
            }
            if ((int) $this->input('customer_id') !== $sale->customer_id) {
                $validator->errors()->add('customer_id', 'Customer cannot be changed.');
            }
            if ($this->input('sale_date') !== $sale->sale_date->format('Y-m-d')) {
                $validator->errors()->add('sale_date', 'Date cannot be changed.');
            }

        });
    }
}
