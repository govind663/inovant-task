<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Checkout (Cart → Order)
     */
    public function checkout(): JsonResponse
    {
        return DB::transaction(function () {

            // Get active cart
            $cart = Cart::with('items.product')
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            // Validate cart
            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $cart->total_amount,
                'status' => 'pending',
            ]);

            // Create Order Items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Update cart status
            $cart->update([
                'status' => 'checked_out'
            ]);

            // OPTIONAL: clear cart items
            $cart->items()->delete();

            // Load relationships
            $order->load(['items.product', 'payment']);

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => new OrderResource($order)
            ]);
        });
    }
}