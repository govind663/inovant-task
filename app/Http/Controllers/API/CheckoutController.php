<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

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
            ]);

        } catch (Exception $e) {

            Log::error('Checkout Failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getCode() === 400 
                    ? $e->getMessage() 
                    : 'Checkout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $e->getCode() === 400 ? 400 : 500);
        }
    }
}