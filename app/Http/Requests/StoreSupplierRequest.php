<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:suppliers,email'],
            'mobile' => ['required', 'string', 'max:30', 'unique:suppliers,phone'],
            'address' => ['nullable', 'string'],
        ];
    }
}

