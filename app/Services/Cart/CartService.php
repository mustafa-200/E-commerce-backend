<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Exceptions\InsufficientStockException;  
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * يجيب سلة الـ Guest الحالية، أو ينشئ واحدة جديدة لو مش موجودة
     */
    public function getOrCreateCart(?int $userId, ?string $sessionId): Cart
    {
        if ($userId) {
            return Cart::firstOrCreate(['user_id' => $userId]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function getCart(?int $userId, ?string $sessionId): ?Cart
    {
        $query = $userId
            ? Cart::where('user_id', $userId)
            : Cart::where('session_id', $sessionId);

        return $query->with('items.variant.product', 'items.variant.attributeValues')->first();
    }

    public function addItem(Cart $cart, int $variantId, int $quantity): CartItem
    {
        $variant = ProductVariant::findOrFail($variantId);

        $existingItem = $cart->items()->where('product_variant_id', $variantId)->first();
        $newQuantity = $existingItem ? $existingItem->quantity + $quantity : $quantity;

        $this->ensureStockAvailable($variant, $newQuantity);

        if ($existingItem) {
            $existingItem->update(['quantity' => $newQuantity]);
            return $existingItem;
        }

        return $cart->items()->create([
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $this->ensureStockAvailable($item->variant, $quantity);

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * يدمج سلة الـ Guest مع سلة المستخدم بعد تسجيل الدخول/إنشاء الحساب
     */
    public function mergeGuestCartIntoUser(string $sessionId, User $user): void
    {
        $guestCart = Cart::with('items.variant')->where('session_id', $sessionId)->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return; // مفيش سلة Guest أصلاً، أو فاضية، مفيش داعي نعمل حاجة
        }

        DB::transaction(function () use ($guestCart, $user) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                // لو الـ Variant اتمسح نهائيًا (مش Soft Delete بس)، تجاهل السطر ده بأمان
                if (!$guestItem->variant) {
                    continue;
                }

                $existingItem = $userCart->items()
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                $mergedQuantity = $existingItem
                    ? $existingItem->quantity + $guestItem->quantity
                    : $guestItem->quantity;

                // لازم نتأكد من المخزون هنا برضو، وإلا الدمج ممكن يخلي
                // الكمية النهائية تتخطى المتاح فعليًا في المخزن
                $this->ensureStockAvailable($guestItem->variant, $mergedQuantity);

                if ($existingItem) {
                    $existingItem->update(['quantity' => $mergedQuantity]);
                } else {
                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    private function ensureStockAvailable(ProductVariant $variant, int $requestedQuantity): void
    {
        if ($variant->stock_quantity < $requestedQuantity) {
            throw new InsufficientStockException(
                "الكمية المطلوبة غير متوفرة. المتاح حاليًا: {$variant->stock_quantity} قطعة فقط."
            );
        }
    }

    /**
     * يتأكد إن الـ CartItem ده فعليًا بتاع نفس الشخص (مسجل أو Guest)
     * اللي بعت الـ Request. ده اللي بيمنع أي حد يعدل/يمسح سلة حد تاني
     * (IDOR) بمجرد ما يعرف/يخمن رقم الـ CartItem.
     */
    public function itemBelongsTo(CartItem $item, ?int $userId, ?string $sessionId): bool
    {
        $cart = $item->cart;

        if (!$cart) {
            return false;
        }

        if ($userId) {
            return $cart->user_id === $userId;
        }

        return $sessionId !== null && $cart->session_id === $sessionId;
    }
}