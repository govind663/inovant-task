<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderController extends Controller
{
    /**
     * Get logged-in user order list
     */
    public function index(): JsonResponse
    {
        try {
            $orders = Order::with(['items.product', 'payment'])
                ->where('user_id', Auth::id())
                ->latest()
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Order list fetched successfully',
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get single order (SECURE)
     */
    public function show($id): JsonResponse
    {
        try {
            $order = Order::with(['items.product', 'payment'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order fetched successfully',
                'data' => new OrderResource($order)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cancel Order
     */
    public function cancel($id): JsonResponse
    {
        try {
            $order = Order::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            /**
             *  Only pending orders can be cancelled
             */
            if ($order->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only pending orders can be cancelled'
                ], 400);
            }

            /**
             *  Prevent cancel if already paid
             */
            if ($order->is_paid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Paid order cannot be cancelled'
                ], 400);
            }

            $order->update([
                'status' => 'cancelled'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order cancellation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}