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
     * =========================
     * Initiate Payment
     * =========================
     */
    public function initiate(int $orderId): array
    {
        try {
            return DB::transaction(function () use ($orderId) {

                $userId = Auth::id();

                if (!$userId) {
                    throw new Exception('Unauthorized user', 401);
                }

                $order = Order::where('id', $orderId)
                    ->where('user_id', $userId)
                    ->first();

                if (!$order) {
                    throw new Exception('Order not found', 404);
                }

                if ($order->is_paid) {
                    throw new Exception('Order already paid', 400);
                }

                if ($order->status !== 'pending') {
                    throw new Exception('Only pending orders can be paid', 400);
                }

                /**
                 * ✅ Prevent duplicate payment
                 */
                $existing = Payment::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->first();

                if ($existing) {
                    return [
                        'message' => 'Payment already initiated',
                        'data' => [
                            'razorpay_order_id' => $existing->razorpay_order_id,
                            'amount' => $existing->amount,
                            'payment_id' => $existing->id
                        ]
                    ];
                }

                /**
                 * ✅ Create Razorpay Order
                 */
                $rzpOrder = $this->razorpay->createOrder($order->total_amount);

                /**
                 * ✅ Save Payment
                 */
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'gateway' => 'razorpay',
                    'razorpay_order_id' => $rzpOrder['id'], // 🔥 important
                ]);

                Log::info('Payment Initiated', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id
                ]);

                return [
                    'message' => 'Payment initiated successfully',
                    'data' => [
                        'razorpay_order_id' => $rzpOrder['id'],
                        'amount' => $rzpOrder['amount'] / 100,
                        'currency' => $rzpOrder['currency'],
                        'payment_id' => $payment->id
                    ]
                ];
            });

        } catch (Exception $e) {

            Log::error('Payment Initiation Failed', [
                'user_id' => Auth::id(),
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            throw new Exception(
                in_array($e->getCode(), [400, 401, 404])
                    ? $e->getMessage()
                    : 'Unable to create payment order.',
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * =========================
     * Verify Payment
     * =========================
     */
    public function verify(array $data): Payment
    {
        return DB::transaction(function () use ($data) {

            /**
             * ✅ Validate input
             */
            if (
                empty($data['razorpay_order_id']) ||
                empty($data['razorpay_payment_id']) ||
                empty($data['razorpay_signature'])
            ) {
                throw new Exception('Invalid payment data', 400);
            }

            /**
             * ✅ Find payment by razorpay_order_id
             */
            $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])
                ->with('order')
                ->first();

            if (!$payment) {
                throw new Exception('Payment not found', 404);
            }

            /**
             * ✅ Auth check
             */
            if ($payment->order->user_id !== Auth::id()) {
                throw new Exception('Unauthorized access', 403);
            }

            /**
             * 🔥 TEST MODE
             */
            $isValid = true;

            /**
             * ✅ PRODUCTION MODE
             */
            // $isValid = $this->razorpay->verifySignature($data);

            if (!$isValid) {
                throw new Exception('Invalid payment signature', 400);
            }

            /**
             * ✅ Already success
             */
            if ($payment->status === 'success') {
                return $payment->fresh('order');
            }

            /**
             * ✅ Update payment
             */
            $payment->update([
                'status' => 'success',
                'response' => $data,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ]);

            /**
             * ✅ Update order
             */
            $payment->order->update([
                'is_paid' => true,
                'status' => 'paid'
            ]);

            Log::info('Payment Success', [
                'payment_id' => $payment->id
            ]);

            return $payment->fresh('order');
        });
    }

    /**
     * =========================
     * Mark Payment Failed
     * =========================
     */
    public function markFailed(int $paymentId, array $data): Payment
    {
        return DB::transaction(function () use ($paymentId, $data) {

            $payment = Payment::with('order')
                ->where('id', $paymentId)
                ->first();

            if (!$payment) {
                throw new Exception('Payment not found', 404);
            }

            if ($payment->order->user_id !== Auth::id()) {
                throw new Exception('Unauthorized access', 403);
            }

            if ($payment->status === 'failed') {
                return $payment->fresh('order');
            }

            /**
             * ✅ Update payment
             */
            $payment->update([
                'status' => 'failed',
                'response' => $data
            ]);

            /**
             * ✅ Update order
             */
            $payment->order->update([
                'status' => 'failed',
                'is_paid' => false
            ]);

            Log::info('Payment Failed', [
                'payment_id' => $payment->id
            ]);

            return $payment->fresh('order');
        });
    }
}