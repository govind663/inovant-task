<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PayRequest;
use App\Http\Requests\Payment\PaymentStatusRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    protected PaymentService $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    /**
     * Start Payment
     */
    public function pay(PayRequest $request): JsonResponse
    {
        try {

            $result = $this->service->initiate($request->order_id);

            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);

        } catch (Exception $e) {

            Log::error('Payment Initiation Failed', [
                'user_id' => Auth::id(),
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getCode() === 400
                    ? $e->getMessage()
                    : 'Payment initiation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $e->getCode() === 400 ? 400 : 500);
        }
    }

    /**
     * Payment Success
     */
    public function success(Request $request): JsonResponse
    {
        try {

            $result = $this->service->verify($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => new PaymentResource($result)
            ]);

        } catch (Exception $e) {

            Log::error('Payment Verification Failed', [
                'user_id' => Auth::id(),
                'payload' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getCode() === 400
                    ? $e->getMessage()
                    : 'Payment verification failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $e->getCode() === 400 ? 400 : 500);
        }
    }

    /**
     * Payment Failed
     */
    public function failed(PaymentStatusRequest $request): JsonResponse
    {
        try {

            $payment = $this->service->markFailed(
                $request->payment_id,
                $request->all()
            );

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'data' => new PaymentResource($payment)
            ]);

        } catch (Exception $e) {

            Log::error('Payment Fail Handling Error', [
                'user_id' => Auth::id(),
                'payment_id' => $request->payment_id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getCode() === 400
                    ? $e->getMessage()
                    : 'Payment failure handling failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], $e->getCode() === 400 ? 400 : 500);
        }
    }
}