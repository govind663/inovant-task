<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'transaction_id' => $this->transaction_id,

            'order' => [
                'id' => $this->order?->id,
                'total_amount' => (float) $this->order?->total_amount,
                'status' => $this->order?->status,
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}