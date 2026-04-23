<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,

            'total_items' => (int) $this->items_count,
            'total_amount' => (float) $this->total_amount,

            'status' => (string) $this->status,

            // ✅ Better than is_paid
            'payment_status' => $this->is_paid ? 'paid' : 'pending',

            'created_at' => $this->created_at?->toDateTimeString(),

            // ✅ Items
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => (int) $item->id,
                        'quantity' => (int) $item->quantity,

                        'unit_price' => (float) $item->price,
                        'total' => (float) $item->total_price,

                        'product' => [
                            'id' => (int) $item->product?->id,
                            'name' => (string) $item->product?->name,
                            'price' => (float) $item->product?->price,
                        ]
                    ];
                });
            }),

            // ✅ Payment (only if exists)
            'payment' => $this->whenLoaded('payment', function () {
                return $this->payment ? [
                    'id' => (int) $this->payment->id,
                    'transaction_id' => (string) $this->payment->transaction_id,
                    'gateway' => (string) $this->payment->gateway,
                    'amount' => (float) $this->payment->amount,
                    'status' => (string) $this->payment->status,
                ] : null;
            }),
        ];
    }
}