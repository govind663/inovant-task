<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Get logged-in user order list
     */
    public function index(): JsonResponse
    {
        try {
            $orders = $this->service->list();

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
            ], 200);

        } catch (Exception $e) {

            Log::error('Order List Failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch orders',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get single order
     */
    public function show($id): JsonResponse
    {
        try {
            $order = $this->service->find($id);

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
            ], 200);

        } catch (Exception $e) {

            Log::error('Order Fetch Failed', [
                'order_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

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
            $order = $this->service->cancel($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order' => new OrderResource($order),
                    'cancelled_at' => now()->toDateTimeString()
                ]
            ], 200);

        } catch (Exception $e) {

            Log::error('Order Cancel Failed', [
                'order_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}