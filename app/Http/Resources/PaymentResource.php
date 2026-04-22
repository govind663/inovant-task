<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment' => [
                'id' => (int) $this->id,
                'order_id' => (int) $this->order_id,
                'amount' => (float) $this->amount,
                'status' => (string) $this->status,
                'gateway' => (string) $this->gateway,
                'transaction_id' => $this->transaction_id,
                'created_at' => $this->created_at?->toDateTimeString(),
            ],

            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => (int) $this->order?->id,
                    'total_amount' => (float) $this->order?->total_amount,
                    'status' => (string) $this->order?->status,
                ];
            }),
        ];
    }
}