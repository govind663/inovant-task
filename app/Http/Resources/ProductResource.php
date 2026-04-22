<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'id' => (int) $this->id,
                'name' => (string) $this->name,
                'price' => (float) $this->price,
                'created_at' => $this->created_at?->toDateTimeString(),
            ],

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => (int) $image->id,
                        'url' => asset('storage/' . $image->image_path),
                    ];
                });
            }),
        ];
    }
}