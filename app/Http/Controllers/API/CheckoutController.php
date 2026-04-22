<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
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
            $order = $this->service->checkout();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => new OrderResource($order)
            ], 201); // ✅ better status for creation

        } catch (Exception $e) {

            // ✅ structured logging
            Log::error('Checkout Failed', [
                'user_id' => Auth::id(),
                'exception' => get_class($e),
                'error' => $e->getMessage()
            ]);

            // ✅ safe error handling
            $statusCode = ($e->getCode() >= 400 && $e->getCode() < 500)
                ? $e->getCode()
                : 500;

            return response()->json([
                'status' => false,
                'message' => $statusCode === 400
                    ? $e->getMessage()
                    : 'Checkout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $statusCode);
        }
    }
}