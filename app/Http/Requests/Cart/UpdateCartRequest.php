<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        $itemId = $this->input('item_id');

        return CartItem::where('id', $itemId)
            ->whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))
            ->exists();
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => __('Cart item is required.'),
            'item_id.exists' => __('Invalid cart item.'),
            'quantity.required' => __('Quantity is required.'),
            'quantity.integer' => __('Quantity must be a number.'),
            'quantity.min' => __('Quantity must be at least 1.'),
        ];
    }
}