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

            // Already paid check
            if ($order->is_paid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already paid'
                ], 400);
            }

            // Avoid duplicate pending payment
            $existingPayment = Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'status' => true,
                    'message' => 'Payment already initiated',
                    'data' => new PaymentResource($existingPayment->load('order'))
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'gateway' => 'dummy'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment initiated successfully',
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

            $payment = Payment::with('order')
                ->findOrFail($request->payment_id);

            // Already success check
            if ($payment->status === 'success') {
                return response()->json([
                    'status' => true,
                    'message' => 'Payment already successful',
                    'data' => new PaymentResource($payment)
                ]);
            }

            // Mark payment success
            $payment->markAsSuccess($request->all());

            // Ensure order consistency
            $payment->order->update([
                'is_paid' => true,
                'status' => 'paid'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => new PaymentResource($payment->fresh('order'))
            ]);
        });
    }

    /**
     * Payment Failed
     */
    public function failed(PaymentStatusRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {

            $payment = Payment::with('order')
                ->findOrFail($request->payment_id);

            // Already failed check
            if ($payment->status === 'failed') {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment already failed',
                    'data' => new PaymentResource($payment)
                ]);
            }

            // Mark failed
            $payment->markAsFailed($request->all());

            // Sync order status
            $payment->order->update([
                'is_paid' => false,
                'status' => 'failed'
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'data' => new PaymentResource($payment->fresh('order'))
            ]);
        });
    }
}