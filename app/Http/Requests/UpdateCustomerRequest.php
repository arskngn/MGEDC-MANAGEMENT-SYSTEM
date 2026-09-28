<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var int|null $customerId */
        $customerId = $this->route('customer')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'mobile' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'address' => ['nullable', 'string'],
            'store_category' => ['required', 'string', 'max:255'],
            'route_id' => ['required', 'integer', 'exists:customer_routes,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}

