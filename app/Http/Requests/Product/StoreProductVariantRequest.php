<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'                       => ['required', 'string', 'unique:product_variants,sku'],
            'barcode'                   => ['nullable', 'string'],
            'price'                     => ['required', 'numeric', 'min:0'],
            'sale_price'                => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity'            => ['nullable', 'integer', 'min:0'],
            'is_default'                => ['nullable', 'boolean'],
            'is_active'                 => ['nullable', 'boolean'],
            'attribute_value_ids'       => ['nullable', 'array'],
            'attribute_value_ids.*'     => ['exists:attribute_values,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'سعر الخصم يجب أن يكون أقل من السعر الأساسي.',
            'sku.unique' => 'رمز المنتج (SKU) مستخدم من قبل.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }
}
