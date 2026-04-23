<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class CartService
{
    /**
     * Get active cart
     */
    public function getUserCart()
    {
        return Cart::with('items.product')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();
    }

    /**
     * Add to cart
     */
    public function add($request)
    {
        return DB::transaction(function () use ($request) {

            $quantity = max(1, (int) ($request->quantity ?? 1));

            $product = Product::findOrFail($request->product_id);

            $cart = Cart::firstOrCreate([
                'user_id' => Auth::id(),
                'status' => 'active'
            ]);

            // 🔥 Prevent modification after checkout
            if ($cart->status !== 'active') {
                throw new Exception('Cart is locked');
            }

            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $item->increment('quantity', $quantity);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            // ✅ ALWAYS recalc
            $cart->recalculate();

            return $cart->fresh(['items.product']);
        });
    }

    /**
     * Update cart item
     */
    public function update($request)
    {
        return DB::transaction(function () use ($request) {

            $item = CartItem::where('id', $request->item_id)
                ->whereHas('cart', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->firstOrFail();

            // 🔥 Prevent invalid quantity
            if ($request->quantity <= 0) {
                throw new Exception('Quantity must be greater than 0');
            }

            $item->update([
                'quantity' => $request->quantity
            ]);

            $cart = $item->cart;

            if ($cart->status !== 'active') {
                throw new Exception('Cart is locked');
            }

            $cart->recalculate();

            return $cart->fresh(['items.product']);
        });
    }

    /**
     * Remove item
     */
    public function remove($request)
    {
        return DB::transaction(function () use ($request) {

            $item = CartItem::where('id', $request->item_id)
                ->whereHas('cart', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->firstOrFail();

            $cart = $item->cart;

            if ($cart->status !== 'active') {
                throw new Exception('Cart is locked');
            }

            $item->delete();

            $cart->recalculate();

            return $cart->fresh(['items.product']);
        });
    }
}