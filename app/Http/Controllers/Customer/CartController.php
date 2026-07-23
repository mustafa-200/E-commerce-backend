<?php

namespace App\Http\Controllers\Customer;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Controllers\Controller;
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
        $this->authorizeCartItem($request, $cartItem);

        $this->cartService->updateItemQuantity(
            $cartItem,
            $request->validated()['quantity']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الكمية بنجاح',
            'data' => new CartResource(
                $cartItem->cart->fresh([
                    'items.variant.product',
                    'items.variant.attributeValues',
                ])
            ),
        ]);
    }
    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($request, $cartItem);

        $this->cartService->removeItem($cartItem);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المنتج من السلة بنجاح',
        ]);
    }

    /**
     * يمنع أي مستخدم أو Guest من تعديل أو حذف CartItem لا يخصه.
     */
    private function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        $isOwner = $this->cartService->itemBelongsTo(
            $cartItem,
            $request->user()?->id,
            $request->header('X-Guest-Session-ID')
        );

        abort_if(
            !$isOwner,
            403,
            'غير مصرح لك بالتعامل مع هذا العنصر.'
        );
    }

    public function merge(Request $request)
    {
        $sessionId = $request->header('X-Guest-Session-ID');

        if ($sessionId) {
            $this->cartService->mergeGuestCartIntoUser($sessionId, $request->user());
        }

        $cart = $this->cartService->getCart($request->user()->id, null);

        return response()->json([
            'status' => 'success',
            'message' => 'تم دمج السلة بنجاح',
            'data' => $cart ? new CartResource($cart) : null,
        ]);
    }
}