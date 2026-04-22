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
     * Create Razorpay Order
     */
    public function createOrder(float $amount): array
    {
        try {
            $order = $this->api->order->create([
                'receipt' => 'order_' . now()->timestamp,
                'amount' => (int) round($amount * 100), // safer conversion
                'currency' => config('services.razorpay.currency', 'INR'),
            ]);

            return [
                'id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
            ];

        } catch (\Throwable $e) {

            Log::error('Razorpay Order Creation Failed', [
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            throw new RuntimeException('Unable to create payment order.');
        }
    }

    /**
     * Verify Signature
     */
    public function verifySignature(array $attributes): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;

        } catch (\Throwable $e) {

            Log::error('Razorpay Signature Verification Failed', [
                'order_id' => Arr::get($attributes, 'razorpay_order_id'),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}