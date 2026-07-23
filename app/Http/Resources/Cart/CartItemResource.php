<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $variant = $this->variant;

        if (!$variant) {
            return [
                'id' => $this->id,
                'product' => null,
                'variant' => null,
                'quantity' => $this->quantity,
                'subtotal' => 0,
                'unavailable' => true,
            ];
        }

        $price = $variant->sale_price ?? $variant->price;

        $primaryImage = $variant->product->images->firstWhere('is_primary', true)
            ?? $variant->product->images->first();

        return [
            'id' => $this->id,
            'product' => [
                'id' => $variant->product->id,
                'name' => $variant->product->name,
                'image' => $primaryImage ? asset('storage/' . $primaryImage->image) : null,
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