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
            'id' => $this->id,
            'user_id' => $this->user_id,

            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'is_paid' => $this->is_paid,
            'is_processed' => $this->is_processed,

            'items_count' => $this->items_count,

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,

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
                    'id' => $this->payment?->id,
                    'transaction_id' => $this->payment?->transaction_id,
                    'gateway' => $this->payment?->gateway,
                    'amount' => $this->payment?->amount,
                    'status' => $this->payment?->status,
                ];
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}