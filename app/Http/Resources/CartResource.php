<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart' => [
                'id' => (int) $this->id,
                'total_items' => (int) $this->total_items,
                'total_amount' => (float) $this->total_amount,
                'status' => (string) $this->status,
                'last_activity_at' => $this->last_activity_at?->toDateTimeString(),
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
        ];
    }
}