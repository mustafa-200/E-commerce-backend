<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function show(Request $request)
    {
        $cart = $this->cartService->getCart(
            $request->user()?->id,
            $request->header('X-Guest-Session-ID')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب السلة بنجاح',
            'data' => $cart ? new CartResource($cart) : null,
        ]);
    }

    public function store(AddToCartRequest $request)
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user()?->id,
            $request->header('X-Guest-Session-ID')
        );

        $this->cartService->addItem(
            $cart,
            $request->validated()['product_variant_id'],
            $request->validated()['quantity']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة المنتج للسلة بنجاح',
            'data' => new CartResource($cart->fresh(['items.variant.product', 'items.variant.attributeValues'])),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        $this->cartService->updateItemQuantity($cartItem, $request->validated()['quantity']);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الكمية بنجاح',
            'data' => new CartResource($cartItem->cart->fresh(['items.variant.product', 'items.variant.attributeValues'])),
        ]);
    }

    public function destroy(CartItem $cartItem)
    {
        $this->cartService->removeItem($cartItem);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المنتج من السلة بنجاح',
        ]);
    }
}
