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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],

            'cart' => [
                'cart_id' => $this->id,
                'total_items' => $this->total_items,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'last_activity_at' => $this->last_activity_at?->toDateTimeString(),
            ],

            'items' => $this->items->map(function ($item) {
                return [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                ];
            }),
        ];
    }
}