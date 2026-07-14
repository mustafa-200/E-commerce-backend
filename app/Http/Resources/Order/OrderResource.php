<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'shipping_cost' => (float) $this->shipping_cost,
            'total' => (float) $this->total,
            'address' => new OrderAddressResource($this->whenLoaded('address')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => $this->whenLoaded('statusHistories', function () {
                return $this->statusHistories->map(fn($h) => [
                    'status' => $h->status,
                    'note' => $h->note,
                    'created_at' => $h->created_at->format('Y-m-d H:i'),
                ]);
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
