<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ProductVariantService
{
    public function list(Product $product): Collection
    {
        return $product->variants()->with('attributeValues')->get();
    }

    public function create(Product $product, array $data): ProductVariant
    {
        // لو الـ Variant الجديد Default، نلغي الـ Default من باقي Variants نفس المنتج
        if (!empty($data['is_default'])) {
            $product->variants()->update(['is_default' => false]);
        }

        $variant = $product->variants()->create([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (!empty($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }

        return $variant->load('attributeValues');
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        if (!empty($data['is_default'])) {
            $variant->product->variants()
                ->where('id', '!=', $variant->id)
                ->update(['is_default' => false]);
        }

        $variant->update($data);

        if (isset($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }

        return $variant->load('attributeValues');
    }

    public function delete(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
