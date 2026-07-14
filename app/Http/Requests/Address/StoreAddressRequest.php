<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string'],
            'city' => ['required', 'string'],
            'area' => ['required', 'string'],
            'street' => ['required', 'string'],
            'building' => ['nullable', 'string'],
            'floor' => ['nullable', 'string'],
            'apartment' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
