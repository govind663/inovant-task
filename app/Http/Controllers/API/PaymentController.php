<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PayRequest;
use App\Http\Requests\Payment\PaymentStatusRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Start Payment
     */
    public function pay(PayRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {

            $order = Order::findOrFail($request->order_id);

            // Prevent duplicate payment
            if ($order->is_paid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already paid'
                ], 400);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'gateway' => 'dummy'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment initiated',
                'data' => new PaymentResource($payment->load('order'))
            ]);
        });
    }

    /**
     * Payment Success
     */
    public function success(PaymentStatusRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {

            $payment = Payment::with('order')->findOrFail($request->payment_id);

            // Prevent duplicate success
            if ($payment->status === 'success') {
                return response()->json([
                    'status' => true,
                    'message' => 'Already marked as success'
                ]);
            }

            $payment->markAsSuccess($request->all());

            $payment->order->markAsPaid();

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => new PaymentResource($payment)
            ]);
        });
    }

    /**
     * Payment Failed
     */
    public function failed(PaymentStatusRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {

            $payment = Payment::with('order')->findOrFail($request->payment_id);

            $payment->markAsFailed($request->all());

            $payment->order->markAsFailed();

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'data' => new PaymentResource($payment)
            ]);
        });
    }
}