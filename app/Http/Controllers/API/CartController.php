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

class CartController extends Controller
{
    /**
     * Get user cart
     */
    public function index(): JsonResponse
    {
        $cart = Cart::with('items.product')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        return response()->json([
            'status' => true,
            'data' => $cart ? new CartResource($cart) : null
        ]);
    }

    /**
     * Add to cart
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $quantity = $request->quantity ?? 1;

        // Get or create cart
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
            'status' => 'active'
        ]);

        // Check if item already exists
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        $product = Product::findOrFail($request->product_id);

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

        // Recalculate cart
        $cart->recalculate();

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart',
            'data' => new CartResource($cart->load('items.product'))
        ]);
    }

    /**
     * Update quantity
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::findOrFail($request->item_id);

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
    }

    /**
     * Remove item
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id'
        ]);

        $item = CartItem::findOrFail($request->item_id);

        $cart = $item->cart;

        $item->delete();

        $cart->recalculate();

        return response()->json([
            'status' => true,
            'message' => 'Item removed',
            'data' => new CartResource($cart->load('items.product'))
        ]);
    }
}