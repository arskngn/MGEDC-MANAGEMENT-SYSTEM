<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_no' => ['required', 'string', 'max:64', 'unique:adjustments,tracking_no'],
            'adjustment_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.type' => ['required', 'in:Added,Removed'],
        ];
    }
}
