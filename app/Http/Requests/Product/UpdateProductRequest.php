<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare input
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],

            'price' => 'sometimes|numeric|min:1',

            // optional images
            'images' => 'sometimes|array',
            'images.*' => 'file|image|mimes:jpg,jpeg,png|max:2048',

            // delete selected images (secure)
            'delete_images' => 'sometimes|array',
            'delete_images.*' => [
                'exists:product_images,id',
                // ensure image belongs to this product
                Rule::exists('product_images', 'id')->where(function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => __('Product name must be a string.'),
            'name.max' => __('Product name must be less than 255 characters.'),
            'name.unique' => __('Product with this name already exists.'),

            'price.numeric' => __('Product price must be a number.'),
            'price.min' => __('Product price must be at least 1.'),

            'images.array' => __('Images must be an array.'),
            'images.*.file' => __('Each upload must be a valid file.'),
            'images.*.image' => __('Each file must be an image.'),
            'images.*.mimes' => __('Each image must be a file of type: jpg, jpeg, png.'),
            'images.*.max' => __('Each image must not exceed 2MB in size.'),

            'delete_images.array' => __('Delete images must be an array.'),
            'delete_images.*.exists' => __('Selected image does not exist or does not belong to this product.'),
        ];
    }
}