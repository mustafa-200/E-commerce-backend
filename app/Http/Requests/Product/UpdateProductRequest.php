<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id'       => ['sometimes', 'required', 'exists:categories,id'],
            'brand_id'          => ['nullable', 'exists:brands,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'is_featured'       => ['nullable', 'boolean'],
            'is_best_seller'    => ['nullable', 'boolean'],
            'is_active'         => ['nullable', 'boolean'],
        ];
    }
}
