<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),

            'cart' => [
                'cart_id' => $this->id,
                'total_items' => $this->total_items,
                'total_amount' => (float) $this->total_amount,
                'status' => $this->status,
                'last_activity_at' => $this->last_activity_at?->toDateTimeString(),
            ],

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'price' => (float) $item->price,
                        'quantity' => (int) $item->quantity,
                        'total_price' => (float) $item->total_price,
                    ];
                });
            }),
        ];
    }
}