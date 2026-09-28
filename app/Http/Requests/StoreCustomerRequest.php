<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'mobile' => ['nullable', 'string', 'max:30', 'unique:customers,phone'],
            'address' => ['nullable', 'string'],
            'store_category' => ['required', 'string', 'max:255'],
            'route_id' => ['required', 'integer', 'exists:customer_routes,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}

