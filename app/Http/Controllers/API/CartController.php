<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartController extends Controller
{
    /**
     * Get user cart
     */
    public function index(): JsonResponse
    {
        try {
            $cart = Cart::with('items.product')
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            return response()->json([
                'status' => true,
                'data' => $cart ? new CartResource($cart) : null
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch cart',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Add to cart
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $quantity = $request->quantity ?? 1;

            $product = Product::findOrFail($request->product_id);

            // Get or create cart
            $cart = Cart::firstOrCreate([
                'user_id' => Auth::id(),
                'status' => 'active'
            ]);

            // Check if item already exists
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

            return response()->json([
                'status' => true,
                'message' => 'Product added to cart',
                'data' => new CartResource($cart->load('items.product'))
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add product to cart',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update quantity
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1'
            ]);

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

            return response()->json([
                'status' => true,
                'message' => 'Cart updated',
                'data' => new CartResource($cart->load('items.product'))
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Cart update failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove item
     */
    public function remove(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:cart_items,id'
            ]);

            $item = CartItem::where('id', $request->item_id)
                ->whereHas('cart', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->firstOrFail();

            $cart = $item->cart;

            $item->delete();

            $cart->recalculate();

            return response()->json([
                'status' => true,
                'message' => 'Item removed',
                'data' => new CartResource($cart->load('items.product'))
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}