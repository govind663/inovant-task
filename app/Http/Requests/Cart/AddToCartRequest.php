<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check(); 
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => __('Product is required.'),
            'product_id.exists' => __('Invalid product selected.'),
            'quantity.integer' => __('Quantity must be a number.'),
            'quantity.min' => __('Quantity must be at least 1.'),
        ];
    }
}