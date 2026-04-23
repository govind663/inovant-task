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

                    $unitPrice = (float) $item->price;
                    $quantity = (int) $item->quantity;

                    return [
                        'id' => (int) $item->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => (float) ($unitPrice * $quantity),

                        // SAFE product mapping
                        'product' => $item->product ? [
                            'id' => (int) $item->product->id,
                            'name' => (string) $item->product->name,
                            'price' => (float) $item->product->price,
                        ] : null
                    ];
                })->values();
            }, []),
        ];
    }
}