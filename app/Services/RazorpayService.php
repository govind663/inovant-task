<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use RuntimeException;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret)) {
            throw new RuntimeException('Razorpay credentials not configured properly.');
        }

        $this->api = new Api($key, $secret);
    }

    /**
     * =========================
     * Create Razorpay Order
     * =========================
     */
    public function createOrder(float $amount): array
    {
        try {

            if ($amount <= 0) {
                throw new RuntimeException('Invalid payment amount.');
            }

            // ✅ Convert to paise (₹ → paisa)
            $amountInPaise = (int) round($amount * 100);

            $order = $this->api->order->create([
                'receipt' => 'order_' . uniqid(),
                'amount' => $amountInPaise,
                'currency' => config('services.razorpay.currency', 'INR'),
                'payment_capture' => 1,
            ]);

            return [
                'id' => $order['id'],
                'amount' => $order['amount'], // paise
                'currency' => $order['currency'],
            ];

        } catch (\Throwable $e) {

            Log::error('Razorpay Order Creation Failed', [
                'amount' => $amount,
                'converted_amount' => $amountInPaise ?? null,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Unable to create payment order.');
        }
    }

    /**
     * =========================
     * Verify Signature
     * =========================
     */
    public function verifySignature(array $attributes): bool
    {
        try {

            // ✅ Strict validation
            $required = [
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature'
            ];

            foreach ($required as $field) {
                if (empty($attributes[$field])) {
                    throw new RuntimeException("Missing field: {$field}");
                }
            }

            // ✅ Verify using Razorpay SDK
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $attributes['razorpay_order_id'],
                'razorpay_payment_id' => $attributes['razorpay_payment_id'],
                'razorpay_signature' => $attributes['razorpay_signature'],
            ]);

            return true;

        } catch (\Throwable $e) {

            Log::error('Razorpay Signature Verification Failed', [
                'order_id' => Arr::get($attributes, 'razorpay_order_id'),
                'payment_id' => Arr::get($attributes, 'razorpay_payment_id'),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}