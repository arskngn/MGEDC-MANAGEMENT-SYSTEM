<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($product->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product->id)],
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
