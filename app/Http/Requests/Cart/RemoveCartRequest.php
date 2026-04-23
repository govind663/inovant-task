<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class RemoveCartRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        $itemId = $this->route('item_id'); // ✅ FIX

        // ❗ Safety check
        if (!$itemId) {
            return false;
        }

        return CartItem::where('id', $itemId)
            ->whereHas('cart', fn($q) => $q->where('user_id', Auth::id()))
            ->exists();
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:cart_items,id'],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'item_id.required' => __('Cart item is required.'),
            'item_id.exists' => __('Invalid cart item.'),
        ];
    }

    /**
     * Prepare Data Before Validation
     */
    protected function prepareForValidation(): void
    {
        // ✅ Route se item_id ko request me inject karo
        $this->merge([
            'item_id' => $this->route('item_id')
        ]);
    }
}