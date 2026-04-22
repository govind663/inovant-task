<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function checkout()
    {
        return DB::transaction(function () {

            // Get active cart
            $cart = Cart::with('items.product')
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception('Cart is empty', 400);
            }

            if ($cart->status !== 'active') {
                throw new \Exception('Cart already checked out', 400);
            }

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $cart->total_amount,
                'status' => 'pending',
            ]);

            // Create Order Items
            foreach ($cart->items as $item) {

                if (!$item->product) {
                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Update cart
            $cart->update([
                'status' => 'checked_out'
            ]);

            // Clear items
            $cart->items()->delete();

            return $order->load(['items.product', 'payment']);
        });
    }
}