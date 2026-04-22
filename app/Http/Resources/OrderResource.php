<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'order' => [
                'id' => (int) $this->id,
                'user_id' => (int) $this->user_id,
                'total_amount' => (float) $this->total_amount,
                'status' => (string) $this->status,
                'is_paid' => (bool) $this->is_paid,
                'is_processed' => (bool) $this->is_processed,
                'items_count' => (int) $this->items_count,
                'created_at' => $this->created_at?->toDateTimeString(),
            ],

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => (int) $item->id,
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'total_price' => (float) $item->total_price,

                        'product' => [
                            'id' => $item->product?->id,
                            'name' => $item->product?->name,
                            'price' => $item->product?->price,
                        ]
                    ];
                });
            }),

            'payment' => $this->whenLoaded('payment', function () {
                return [
                    'id' => (int) $this->payment?->id,
                    'transaction_id' => $this->payment?->transaction_id,
                    'gateway' => $this->payment?->gateway,
                    'amount' => (float) $this->payment?->amount,
                    'status' => $this->payment?->status,
                ];
            }),
        ];
    }
}