<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var int|null $supplierId */
        $supplierId = $this->route('supplier')?->id;

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],
            'mobile' => [
                'required',
                'string',
                'max:30',
                Rule::unique('suppliers', 'phone')->ignore($supplierId),
            ],
            'address' => ['nullable', 'string'],
        ];
    }
}

