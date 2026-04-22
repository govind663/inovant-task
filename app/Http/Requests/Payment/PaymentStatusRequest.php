<?php

namespace App\Http\Requests\Payment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;

class PaymentStatusRequest extends FormRequest
{
    /**
     * Allow only owner of payment (via order)
     */
    public function authorize(): bool
    {
        // User must be logged in
        if (!Auth::check()) {
            return false;
        }

        // Get payment_id safely
        $paymentId = $this->input('payment_id');

        if (!$paymentId) {
            return false;
        }

        // Check ownership via order relation
        return Payment::where('id', $paymentId)
            ->whereHas('order', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->exists();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'payment_id' => 'required|exists:payments,id'
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'payment_id.required' => __('Payment ID is required.'),
            'payment_id.exists' => __('Invalid payment selected.'),
        ];
    }
}