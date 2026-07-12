<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant')->id;

        return [
            'sku'                   => ['sometimes', 'required', 'string', Rule::unique('product_variants', 'sku')->ignore($variantId)],
            'barcode'               => ['nullable', 'string'],
            'price'                 => ['sometimes', 'required', 'numeric', 'min:0'],
            'sale_price'            => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity'        => ['nullable', 'integer', 'min:0'],
            'is_default'            => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
            'attribute_value_ids'   => ['nullable', 'array'],
            'attribute_value_ids.*' => ['exists:attribute_values,id'],
        ];
    }
}
