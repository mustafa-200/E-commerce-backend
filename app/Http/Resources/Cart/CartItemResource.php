<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $variant = $this->variant;
        $price = $variant->sale_price ?? $variant->price;

        return [
            'id' => $this->id,
            'product' => [
                'id' => $variant->product->id,
                'name' => $variant->product->name,
            ],
            'variant' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $price,
                'attributes' => $variant->attributeValues->map(fn($av) => [
                    'attribute' => $av->attribute->name,
                    'value' => $av->value,
                ]),
            ],
            'quantity' => $this->quantity,
            'subtotal' => (float) ($price * $this->quantity),
        ];
    }
}
