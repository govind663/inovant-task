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
        if (!Auth::check()) {
            return false;
        }

        $payment = Payment::where('id', $this->input('payment_id'))
            ->with('order')
            ->first();

        if (!$payment) {
            return false;
        }

        if ($payment->order->user_id !== Auth::id()) {
            return false;
        }

        if ($payment->status !== 'pending') {
            abort(403, 'Only pending payments can be updated.');
        }

        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'payment_id' => 'required|exists:payments,id',
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