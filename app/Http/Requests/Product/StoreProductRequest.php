<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array<string, string>
     */    
    public function messages(): array
    {        
        return [
            'name.required' => __('Product name is required.'),
            'name.string' => __('Product name must be a string.'),
            'name.max' => __('Product name must be less than 255 characters.'),

            'price.required' => __('Product price is required.'),
            'price.numeric' => __('Product price must be a number.'),
            'price.min' => __('Product price must be at least 1.'),

            'images.required' => __('At least one product image is required.'),
            'images.array' => __('Images must be an array.'),
            'images.*.image' => __('Each file must be an image.'),
            'images.*.mimes' => __('Each image must be a file of type: jpg, jpeg, png.'),
            'images.*.max' => __('Each image must not exceed 2MB in size.'),
        ];
    }
}