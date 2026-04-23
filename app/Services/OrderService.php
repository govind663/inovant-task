<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Get user orders
     */
    public function list()
    {
        return Order::with(['items.product', 'payment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
    }

    /**
     * Get single order
     */
    public function find($id): ?Order
    {
        return Order::with(['items.product', 'payment'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Cancel order
     */
    public function cancel($id): ?Order
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return null;
        }

        if ($order->status !== 'pending') {
            throw new \Exception('Only pending orders can be cancelled');
        }

        if ($order->is_paid) {
            throw new \Exception('Paid order cannot be cancelled');
        }

        $order->update([
            'status' => 'cancelled'
        ]);

        Log::info('Order Cancelled', [
            'user_id' => Auth::id(),
            'order_id' => $order->id
        ]);

        return $order;
    }
}