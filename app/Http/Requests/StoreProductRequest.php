<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $hasExpiry = $this->boolean('has_expiry');

        $this->merge([
            'has_expiry' => $hasExpiry,
            // Expiry-tracked products must always use batch tracking.
            'requires_batch_tracking' => $hasExpiry ? true : $this->boolean('requires_batch_tracking'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')],
            'unit_id' => ['required', 'exists:units,id'],
            'alert_quantity' => ['required', 'numeric', 'min:0'],
            'fixed_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'has_expiry' => ['nullable', 'boolean'],
            'shelf_life_days' => ['nullable', 'integer', 'min:1'],
            'expiry_alert_days' => ['nullable', 'integer', 'min:1'],
            'requires_batch_tracking' => ['nullable', 'boolean'],
        ];
    }
}
