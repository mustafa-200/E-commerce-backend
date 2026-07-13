<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = $this->items;

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'total' => (float) $items->sum(fn($item) => ($item->variant->sale_price ?? $item->variant->price) * $item->quantity),
            'items_count' => $items->sum('quantity'),
        ];
    }
}
