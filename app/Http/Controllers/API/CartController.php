<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Requests\Cart\RemoveCartRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class CartController extends Controller
{
    protected CartService $service;

    public function __construct(CartService $service)
    {
        $this->service = $service;
    }

    /**
     * Get user cart
     */
    public function index(): JsonResponse
    {
        try {
            $cart = $this->service->getUserCart();

            return response()->json([
                'status' => true,
                'data' => $cart ? new CartResource($cart) : null
            ]);

        } catch (Exception $e) {
            Log::error('Cart Fetch Failed', [
                'error' => $e->getMessage()
            ]);

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
    public function add(AddToCartRequest $request): JsonResponse
    {
        try {
            $cart = $this->service->add($request);

            return response()->json([
                'status' => true,
                'message' => 'Product added to cart',
                'data' => new CartResource($cart)
            ]);

        } catch (Exception $e) {
            Log::error('Add to Cart Failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to add product to cart',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update cart
     */
    public function update(UpdateCartRequest $request): JsonResponse
    {
        try {
            $cart = $this->service->update($request);

            return response()->json([
                'status' => true,
                'message' => 'Cart updated',
                'data' => new CartResource($cart)
            ]);

        } catch (Exception $e) {
            Log::error('Cart Update Failed', [
                'error' => $e->getMessage()
            ]);

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
    public function remove(RemoveCartRequest $request): JsonResponse
    {
        try {
            $cart = $this->service->remove($request);

            return response()->json([
                'status' => true,
                'message' => 'Item removed',
                'data' => new CartResource($cart)
            ]);

        } catch (Exception $e) {
            Log::error('Cart Remove Failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}