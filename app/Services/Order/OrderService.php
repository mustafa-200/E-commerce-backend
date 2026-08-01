<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Exceptions\OutOfStockAtCheckoutException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createFromCart(Cart $cart, int $addressId, string $paymentMethod, int $userId): Order
    {
        if ($cart->items->isEmpty()) {
            throw new OutOfStockAtCheckoutException('السلة فارغة، لا يمكن إتمام الطلب.');
        }

        $address = Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return DB::transaction(function () use ($cart, $address, $paymentMethod, $userId) {

            $subtotal = 0;
            $orderItemsData = [];

            foreach ($cart->items as $cartItem) {
                $variant = ProductVariant::with([
                    'product',
                    'attributeValues',
                ])
                    ->lockForUpdate()
                    ->find($cartItem->product_variant_id);

                if (!$variant || $variant->stock_quantity < $cartItem->quantity) {
                    throw new OutOfStockAtCheckoutException(
                        "الكمية المطلوبة من \"{$variant?->sku}\" غير متوفرة حاليًا."
                    );
                }

                $unitPrice = $variant->sale_price ?? $variant->price;
                $totalPrice = $unitPrice * $cartItem->quantity;
                $subtotal += $totalPrice;

                $orderItemsData[] = [
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->product->name,
                    'sku_snapshot' => $variant->sku,
                    'variant_snapshot' => $variant->attributeValues->map(fn($av) => $av->value)->implode(' - '),
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];

                $variant->decrement('stock_quantity', $cartItem->quantity);
            }

            $shippingCost = 0;
            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'order_status' => OrderStatus::Pending->value,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_cost' => $shippingCost,
                'total' => $total,
            ]);

            $order->address()->create([
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'city' => $address->city,
                'area' => $address->area,
                'street' => $address->street,
                'building' => $address->building,
                'floor' => $address->floor,
                'apartment' => $address->apartment,
                'notes' => $address->notes,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::Pending->value,
                'changed_by' => $userId,
            ]);

            $cart->items()->delete();

            return $order->load(['items', 'address', 'statusHistories']);
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'DS-' . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    public function listForUser(int $userId)
    {
        return Order::where('user_id', $userId)
            ->with(['items', 'address'])
            ->latest()
            ->paginate(10);
    }

    public function findForUser(int $orderId, int $userId): Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->with(['items', 'address', 'statusHistories'])
            ->firstOrFail();
    }

    public function listAll(?string $status = null)
    {
        return Order::with([
            'user',
            'address',
            'items',
        ])
            ->when($status, function ($query) use ($status) {
                $query->where('order_status', $status);
            })
            ->latest()
            ->paginate(15);
    }

    public function findWithDetails(int $orderId): Order
    {
        return Order::with([
            'user',
            'address',
            'items',
            'statusHistories',
        ])->findOrFail($orderId);
    }

    private const TRANSITIONS = [

        OrderStatus::Pending->value => [
            OrderStatus::Confirmed->value,
            OrderStatus::Cancelled->value,
        ],

        OrderStatus::Confirmed->value => [
            OrderStatus::Preparing->value,
            OrderStatus::Cancelled->value,
        ],

        OrderStatus::Preparing->value => [
            OrderStatus::Packed->value,
        ],

        OrderStatus::Packed->value => [
            OrderStatus::Shipped->value,
        ],

        OrderStatus::Shipped->value => [
            OrderStatus::Delivered->value,
        ],

        OrderStatus::Delivered->value => [],

        OrderStatus::Cancelled->value => [],
    ];


    public function updateStatus(
        Order $order,
        string $status,
        ?string $note,
        int $changedBy
    ): Order {

        $currentStatus = $order->order_status;

        if (
            !in_array(
                $status,
                self::TRANSITIONS[$currentStatus] ?? []
            )
        ) {
            throw new InvalidOrderStatusTransitionException(
                "لا يمكن تحويل الطلب من {$currentStatus} إلى {$status}"
            );
        }

        DB::transaction(function () use ($order, $status, $note, $changedBy) {

            $order->update([
                'order_status' => $status,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'note' => $note,
                'changed_by' => $changedBy,
            ]);

        });

        return $order->fresh([
            'user',
            'address',
            'items',
            'statusHistories',
        ]);
    }

    public function updateShippingCost(Order $order, float $shippingCost): Order
    {
        if (in_array($order->order_status, [
            OrderStatus::Delivered->value,
            OrderStatus::Cancelled->value,
        ])) {
            throw new InvalidOrderStatusTransitionException(
                'لا يمكن تعديل سعر الشحن بعد اكتمال الطلب أو إلغاؤه.'
            );
        }

        return DB::transaction(function () use ($order, $shippingCost) {

            $newTotal = $order->subtotal - $order->discount + $shippingCost;

            $order->update([
                'shipping_cost' => $shippingCost,
                'total' => $newTotal,
            ]);

            return $order->fresh([
                'user',
                'address',
                'items',
                'statusHistories',
            ]);
        });
    }
}