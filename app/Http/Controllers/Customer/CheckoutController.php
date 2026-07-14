<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService
    ) {
    }

    public function store(CheckoutRequest $request)
    {
        $cart = $this->cartService->getCart($request->user()->id, null);

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد سلة نشطة لإتمام الطلب.',
            ], 404);
        }

        $order = $this->orderService->createFromCart(
            $cart,
            $request->validated()['address_id'],
            $request->validated()['payment_method'],
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الطلب بنجاح',
            'data' => new OrderResource($order),
        ], 201);
    }
}
