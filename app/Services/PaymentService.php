<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    protected RazorpayService $razorpay;

    public function __construct(RazorpayService $razorpay)
    {
        $this->razorpay = $razorpay;
    }

    /**
     * Initiate Payment
     */
    public function initiate(int $orderId): array
    {
        return DB::transaction(function () use ($orderId) {

            $userId = Auth::id();

            $order = Order::where('id', $orderId)
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($order->is_paid) {
                throw new Exception('Order already paid', 400);
            }

            $existing = Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return [
                    'message' => 'Payment already initiated',
                    'data' => $existing->load('order')
                ];
            }

            $rzpOrder = $this->razorpay->createOrder($order->total_amount);

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'gateway' => 'razorpay',
                'transaction_id' => $rzpOrder['id']
            ]);

            Log::info('Payment Initiated', [
                'order_id' => $order->id,
                'payment_id' => $payment->id
            ]);

            return [
                'message' => 'Payment initiated successfully',
                'data' => [
                    'razorpay_order_id' => $rzpOrder['id'],
                    'amount' => $rzpOrder['amount'],
                    'currency' => $rzpOrder['currency'],
                    'payment' => $payment->load('order')
                ]
            ];
        });
    }

    /**
     * Verify Payment
     */
    public function verify(array $data): Payment
    {
        return DB::transaction(function () use ($data) {

            $payment = Payment::where('transaction_id', $data['razorpay_order_id'])
                ->with('order')
                ->firstOrFail();

            if ($payment->order->user_id !== Auth::id()) {
                throw new Exception('Unauthorized access', 403);
            }

            $isValid = $this->razorpay->verifySignature($data);

            if (!$isValid) {
                throw new Exception('Invalid payment signature', 400);
            }

            if ($payment->status === 'success') {
                return $payment;
            }

            $payment->markAsSuccess($data);

            Log::info('Payment Success', [
                'payment_id' => $payment->id
            ]);

            return $payment->fresh('order');
        });
    }

    /**
     * Mark Payment Failed
     */
    public function markFailed(int $paymentId, array $data): Payment
    {
        return DB::transaction(function () use ($paymentId, $data) {

            $payment = Payment::with('order')
                ->where('id', $paymentId)
                ->firstOrFail();

            if ($payment->order->user_id !== Auth::id()) {
                throw new Exception('Unauthorized access', 403);
            }

            if ($payment->status === 'failed') {
                return $payment;
            }

            $payment->markAsFailed($data);

            Log::info('Payment Failed', [
                'payment_id' => $payment->id
            ]);

            return $payment->fresh('order');
        });
    }
}