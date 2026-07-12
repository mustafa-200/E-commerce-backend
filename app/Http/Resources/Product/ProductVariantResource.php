<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'sku'           => $this->sku,
            'barcode'       => $this->barcode,
            'price'         => (float) $this->price,
            'sale_price'    => $this->sale_price ? (float) $this->sale_price : null,
            'stock_quantity'=> $this->stock_quantity,
            'is_default'    => $this->is_default,
            'is_active'     => $this->is_active,
            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->map(fn($av) => [
                    'attribute' => $av->attribute->name,
                    'value' => $av->value,
                ]);
            }),
        ];
    }
}
