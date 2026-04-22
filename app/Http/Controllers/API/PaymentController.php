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
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    /**
     * Start Payment (Razorpay Order Create)
     */
    public function pay(PayRequest $request, RazorpayService $razorpay): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $razorpay) {

                $order = Order::where('id', $request->order_id)
                    ->where('user_id', Auth::id()) // ✅ ownership check
                    ->firstOrFail();

                if ($order->is_paid) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order already paid'
                    ], 400);
                }

                // Prevent duplicate pending payment
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

                // Razorpay order create
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

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment initiation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Payment Success (Signature Verify)
     */
    public function success(Request $request, RazorpayService $razorpay): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $razorpay) {

                $request->validate([
                    'razorpay_order_id' => 'required',
                    'razorpay_payment_id' => 'required',
                    'razorpay_signature' => 'required',
                ]);

                $payment = Payment::where('transaction_id', $request->razorpay_order_id)
                    ->with('order')
                    ->firstOrFail();

                // ✅ ownership check
                if ($payment->order->user_id !== Auth::id()) {
                    abort(403, 'Unauthorized access');
                }

                // Signature verify
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

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Payment Failed
     */
    public function failed(PaymentStatusRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {

                $payment = Payment::with('order')
                    ->where('id', $request->payment_id)
                    ->firstOrFail();

                // ✅ ownership check
                if ($payment->order->user_id !== Auth::id()) {
                    abort(403, 'Unauthorized access');
                }

                if ($payment->status === 'failed') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment already failed',
                        'data' => new PaymentResource($payment)
                    ]);
                }

                $payment->markAsFailed($request->all());

                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed',
                    'data' => new PaymentResource($payment->fresh('order'))
                ]);
            });

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment failure handling failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}