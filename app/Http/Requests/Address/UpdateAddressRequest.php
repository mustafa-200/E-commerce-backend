<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'required', 'string'],
            'area' => ['sometimes', 'required', 'string'],
            'street' => ['sometimes', 'required', 'string'],
            'building' => ['nullable', 'string'],
            'floor' => ['nullable', 'string'],
            'apartment' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
