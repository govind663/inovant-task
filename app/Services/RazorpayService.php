<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret)) {
            throw new \Exception('Razorpay credentials not configured properly.');
        }

        $this->api = new Api($key, $secret);
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder($amount)
    {
        return $this->api->order->create([
            'receipt' => 'order_' . now()->timestamp, // better than time()
            'amount' => (int) ($amount * 100), // ensure integer (paise)
            'currency' => config('services.razorpay.currency', 'INR'),
        ]);
    }

    /**
     * Verify Signature
     */
    public function verifySignature(array $attributes): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;

        } catch (\Exception $e) {
            Log::error('Razorpay Signature Verification Failed', [
                'error' => $e->getMessage(),
                'payload' => $attributes
            ]);

            return false;
        }
    }
}