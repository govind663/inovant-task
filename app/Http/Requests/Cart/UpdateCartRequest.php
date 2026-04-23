<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class UpdateCartRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        $itemId = $this->route('item_id');

        // ❗ Extra safety (null check)
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
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'quantity.required' => __('Quantity is required.'),
            'quantity.integer' => __('Quantity must be a valid number.'),
            'quantity.min' => __('Quantity must be at least 1.'),
        ];
    }

    /**
     * Prepare Data Before Validation
     */
    protected function prepareForValidation(): void
    {
        // ✅ Ensure item_id always available (important for service layer)
        $this->merge([
            'item_id' => $this->route('item_id')
        ]);
    }
}