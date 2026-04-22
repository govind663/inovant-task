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
use Exception;

class CheckoutController extends Controller
{
    /**
     * Checkout (Cart → Order)
     */
    public function checkout(): JsonResponse
    {
        try {
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

                /**
                 * Prevent duplicate checkout
                 */
                if ($cart->status !== 'active') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cart already checked out'
                    ], 400);
                }

                /**
                 * Create Order
                 */
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total_amount' => $cart->total_amount,
                    'status' => 'pending',
                ]);

                /**
                 * Create Order Items
                 */
                foreach ($cart->items as $item) {

                    // Optional safety: product existence
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

                /**
                 * Mark cart as checked_out
                 */
                $cart->update([
                    'status' => 'checked_out'
                ]);

                /**
                 * Clear cart items (optional but good)
                 */
                $cart->items()->delete();

                /**
                 * Load relationships
                 */
                $order->load(['items.product', 'payment']);

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'data' => new OrderResource($order)
                ]);
            });

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Checkout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}