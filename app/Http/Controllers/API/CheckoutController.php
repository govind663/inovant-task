<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class CheckoutController extends Controller
{
    protected CheckoutService $service;

    public function __construct(CheckoutService $service)
    {
        $this->service = $service;
    }

    /**
     * Checkout (Cart → Order)
     */
    public function checkout(): JsonResponse
    {
        try {

            // ✅ Step 0: Auth check (IMPORTANT)
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // ✅ Step 1: Direct Cart fetch (NO carts() dependency)
            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'active')
                ->withCount('items')
                ->first();

            // ❌ Empty cart
            if (!$cart || $cart->items_count === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // ✅ Step 2: Checkout
            $order = $this->service->checkout();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => new OrderResource($order)
            ], 201);

        } catch (Exception $e) {

            Log::error('Checkout Failed', [
                'user_id' => Auth::id(),
                'exception' => get_class($e),
                'error' => $e->getMessage()
            ]);

            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 500)
                ? $e->getCode()
                : 500;

            return response()->json([
                'status' => false,
                'message' => $statusCode < 500
                    ? $e->getMessage()
                    : 'Checkout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $statusCode);
        }
    }
}