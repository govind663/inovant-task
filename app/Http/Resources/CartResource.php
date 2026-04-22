<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_items' => $this->total_items,
            'total_amount' => $this->total_amount,
            'status' => $this->status,

            'items' => $this->items->map(function ($item) {
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
            }),

            'last_activity_at' => $this->last_activity_at?->toDateTimeString(),
        ];
    }
}