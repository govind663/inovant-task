<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class PayRequest extends FormRequest
{
    /**
     * Allow only logged-in user who owns the order
     */
    public function authorize(): bool
    {
        // User must be logged in
        if (!Auth::check()) {
            return false;
        }

        // Get order_id safely
        $orderId = $this->input('order_id');

        if (!$orderId) {
            return false;
        }

        // Check ownership using optimized query
        return Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->exists();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id'
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'order_id.required' => __('Order ID is required.'),
            'order_id.exists' => __('Invalid order selected.'),
        ];
    }
}