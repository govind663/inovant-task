<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderController extends Controller
{
    protected $service;

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
            ]);

        } catch (Exception $e) {

            Log::error('Order List Failed', [
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
            ]);

        } catch (Exception $e) {

            Log::error('Order Fetch Failed', [
                'order_id' => $id,
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
            $response = $this->service->cancel($id);

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message']
            ], $response['code']);

        } catch (Exception $e) {

            Log::error('Order Cancel Failed', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Order cancellation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}