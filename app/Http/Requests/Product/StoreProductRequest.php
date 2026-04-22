<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'price' => 'required|numeric|min:1',

            // at least 1 image required
            'images' => 'required|array|min:1',

            // file validation
            'images.*' => 'file|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Product name is required.'),
            'name.string' => __('Product name must be a string.'),
            'name.max' => __('Product name must be less than 255 characters.'),
            'name.unique' => __('Product with this name already exists.'),

            'price.required' => __('Product price is required.'),
            'price.numeric' => __('Product price must be a number.'),
            'price.min' => __('Product price must be at least 1.'),

            'images.required' => __('At least one product image is required.'),
            'images.array' => __('Images must be an array.'),
            'images.min' => __('At least one product image is required.'),

            'images.*.file' => __('Each upload must be a valid file.'),
            'images.*.image' => __('Each file must be an image.'),
            'images.*.mimes' => __('Each image must be a file of type: jpg, jpeg, png.'),
            'images.*.max' => __('Each image must not exceed 2MB in size.'),
        ];
    }
}