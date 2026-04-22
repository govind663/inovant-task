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
        if (!Auth::check()) {
            return false;
        }

        $orderId = $this->input('order_id');

        if (!$orderId) {
            return false;
        }

        // Ownership + Only pending & unpaid orders allowed
        return Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->where('is_paid', false)
            ->exists();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
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