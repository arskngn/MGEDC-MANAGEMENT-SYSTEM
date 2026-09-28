<?php

namespace App\Http\Requests;

use App\Models\StoreCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var StoreCategory $storeCategory */
        $storeCategory = $this->route('store_category');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('store_categories', 'name')->ignore($storeCategory->id)],
        ];
    }
}
