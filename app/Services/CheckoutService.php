<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckoutService
{
    public function checkout()
    {
        return DB::transaction(function () {

            $userId = Auth::id();

            /**
             * Get Active Cart
             */
            $cart = Cart::with('items.product')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            /**
             * Validate Cart
             */
            if (!$cart || $cart->items->isEmpty()) {

                Log::warning('Checkout Failed - Empty Cart', [
                    'user_id' => $userId
                ]);

                throw new Exception('Cart is empty', 400);
            }

            /**
             * Prevent Duplicate Checkout
             */
            if ($cart->status !== 'active') {

                Log::warning('Checkout Failed - Already Processed', [
                    'user_id' => $userId,
                    'cart_id' => $cart->id
                ]);

                throw new Exception('Cart already checked out', 400);
            }

            /**
             * Create Order
             */
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $cart->total_amount,
                'status' => 'pending',
            ]);

            /**
             * Create Order Items
             */
            foreach ($cart->items as $item) {

                if (!$item->product) {
                    Log::warning('Product Missing During Checkout', [
                        'cart_id' => $cart->id,
                        'item_id' => $item->id
                    ]);
                    continue;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            /**
             * Mark Cart as Checked Out
             */
            $cart->update([
                'status' => 'checked_out'
            ]);

            /**
             * Clear Cart Items
             */
            $cart->items()->delete();

            /**
             * Log Success
             */
            Log::info('Checkout Successful', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'amount' => $order->total_amount
            ]);

            return $order->load(['items.product', 'payment']);
        });
    }
}