<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    /**
     * Get all products (latest first)
     */
    public function all()
    {
        return Product::latest()->get();
    }

    /**
     * Get all products with images (explicit eager loading)
     */
    public function withImages()
    {
        return Product::with('images')->latest()->get();
    }

    /**
     * Find product by ID
     */
    public function find(int $id): ?Product
    {
        return Product::find($id);
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