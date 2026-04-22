<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PayRequest;
use App\Http\Requests\Payment\PaymentStatusRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Start Payment (Razorpay Order Create)
     */
    public function pay(PayRequest $request, RazorpayService $razorpay): JsonResponse
    {
        return DB::transaction(function () use ($request, $razorpay) {

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

            // ✅ Razorpay Order Create
            $rzpOrder = $razorpay->createOrder($order->total_amount);

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'gateway' => 'razorpay',
                'transaction_id' => $rzpOrder['id']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'razorpay_order_id' => $rzpOrder['id'],
                    'amount' => $rzpOrder['amount'],
                    'currency' => $rzpOrder['currency'],
                    'payment' => new PaymentResource($payment->load('order'))
                ]
            ]);
        });
    }

    /**
     * Payment Success (Signature Verify)
     */
    public function success(Request $request, RazorpayService $razorpay): JsonResponse
    {
        return DB::transaction(function () use ($request, $razorpay) {

            $request->validate([
                'razorpay_order_id' => 'required',
                'razorpay_payment_id' => 'required',
                'razorpay_signature' => 'required',
            ]);

            $payment = Payment::where('transaction_id', $request->razorpay_order_id)
                ->with('order')
                ->firstOrFail();

            // ✅ Signature verify
            $isValid = $razorpay->verifySignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            if (!$isValid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payment signature'
                ], 400);
            }

            // Already success check
            if ($payment->status === 'success') {
                return response()->json([
                    'status' => true,
                    'message' => 'Payment already successful',
                    'data' => new PaymentResource($payment)
                ]);
            }

            // Mark success
            $payment->markAsSuccess($request->all());

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

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'data' => new PaymentResource($payment->fresh('order'))
            ]);
        });
    }
}