<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    /**
     * Get all products (with pagination & images)
     */
    public function all(int $perPage = 10)
    {
        return Product::with('images')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find product by ID with images
     */
    public function find(int $id): ?Product
    {
        return Product::with('images')->find($id);
    }

    /**
     * Create product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->refresh();
    }

    /**
     * Delete product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}