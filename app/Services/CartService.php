<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            $quantity = $request->quantity ?? 1;

            $product = Product::findOrFail($request->product_id);

            $cart = Cart::firstOrCreate([
                'user_id' => Auth::id(),
                'status' => 'active'
            ]);

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

            $cart->recalculate();

            return $cart->load('items.product');
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

            $item->update([
                'quantity' => $request->quantity
            ]);

            $cart = $item->cart;
            $cart->recalculate();

            return $cart->load('items.product');
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

            $item->delete();

            $cart->recalculate();

            return $cart->load('items.product');
        });
    }
}