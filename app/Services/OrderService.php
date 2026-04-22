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
    public function cancel($id): array
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return [
                'status' => false,
                'message' => 'Order not found',
                'code' => 404
            ];
        }

        if ($order->status !== 'pending') {
            return [
                'status' => false,
                'message' => 'Only pending orders can be cancelled',
                'code' => 400
            ];
        }

        if ($order->is_paid) {
            return [
                'status' => false,
                'message' => 'Paid order cannot be cancelled',
                'code' => 400
            ];
        }

        $order->update([
            'status' => 'cancelled'
        ]);

        Log::info('Order Cancelled', [
            'user_id' => Auth::id(),
            'order_id' => $order->id
        ]);

        return [
            'status' => true,
            'message' => 'Order cancelled successfully',
            'code' => 200
        ];
    }
}