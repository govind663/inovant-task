<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Get logged-in user order list
     */
    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product', 'payment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Get single order (SECURE)
     */
    public function show($id): JsonResponse
    {
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
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Cancel Order (optional but good for machine test)
     */
    public function cancel($id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id()) // ✅ SECURITY
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Only pending orders can be cancelled'
            ], 400);
        }

        $order->update([
            'status' => 'failed'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }
}