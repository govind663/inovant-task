<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Exception;

class AdminCartController extends Controller
{
    /**
     * All carts (CMS)
     */
    public function index(): JsonResponse
    {
        try {
            $carts = Cart::with(['items.product', 'user'])
                ->latest()
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'All carts fetched successfully',
                'data' => CartResource::collection($carts),
                'meta' => [
                    'current_page' => $carts->currentPage(),
                    'last_page' => $carts->lastPage(),
                    'per_page' => $carts->perPage(),
                    'total' => $carts->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch carts',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Single cart
     */
    public function show($id): JsonResponse
    {
        try {
            $cart = Cart::with(['items.product', 'user'])->find($id);

            if (!$cart) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Cart fetched successfully',
                'data' => new CartResource($cart)
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
     * Get specific user cart
     */
    public function showUserCart(User $user): JsonResponse
    {
        try {
            $cart = Cart::with(['items.product'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'User cart fetched successfully',
                'data' => $cart ? new CartResource($cart) : null
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user cart',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}