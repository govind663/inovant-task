<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder($amount)
    {
        return $this->api->order->create([
            'receipt' => 'order_' . time(),
            'amount' => $amount * 100, // paisa में
            'currency' => 'INR'
        ]);
    }

    /**
     * Verify Signature
     */
    public function verifySignature($attributes)
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}