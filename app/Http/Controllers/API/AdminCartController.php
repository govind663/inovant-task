<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminCartController extends Controller
{
    /**
     * All carts (CMS)
     */
    public function index(): JsonResponse
    {
        $carts = Cart::with(['items.product', 'user'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'All carts fetched successfully',
            'data' => CartResource::collection($carts)
        ]);
    }

    /**
     * Single cart
     */
    public function show($id): JsonResponse
    {
        $cart = Cart::with(['items.product', 'user'])->find($id);

        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new CartResource($cart)
        ]);
    }

    /**
     * Machine Test Requirement
     * Get specific user cart
     */
    public function showUserCart(User $user): JsonResponse
    {
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'User cart fetched successfully',
            'data' => $cart ? new CartResource($cart) : null
        ]);
    }
}