<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductRepository;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;

class ProductService
{
    use UploadFile;

    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Store Product
     */
    public function store($request)
    {
        return DB::transaction(function () use ($request) {

            $product = $this->repo->create([
                'name' => $request->name,
                'price' => $request->price,
                'user_id' => 1,
            ]);

            if ($request->hasFile('images')) {

                $paths = $this->uploadMultiple(
                    $request->file('images'),
                    'products'
                );

                foreach ($paths as $path) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }
            }

            return $product->load('images');
        });
    }

    /**
     * Update Product (Partial Image Update)
     */
    public function update($request, Product $product)
    {
        return DB::transaction(function () use ($request, $product) {

            // Update basic fields
            $this->repo->update($product, $request->only('name', 'price'));

            /**
             * ✅ Add New Images (DO NOT delete old)
             */
            if ($request->hasFile('images')) {

                $paths = $this->uploadMultiple(
                    $request->file('images'),
                    'products'
                );

                foreach ($paths as $path) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                    ]);
                }
            }

            /**
             * ✅ Delete Selected Images Only
             */
            if ($request->has('delete_images')) {

                $images = $product->images()
                    ->whereIn('id', $request->delete_images)
                    ->get();

                if ($images->isNotEmpty()) {

                    $paths = $images->pluck('image_path')->toArray();

                    $this->deleteFiles($paths);

                    $product->images()
                        ->whereIn('id', $request->delete_images)
                        ->delete();
                }
            }

            return $product->load('images');
        });
    }

    /**
     * Delete Product (with image cleanup)
     */
    public function delete(Product $product)
    {
        return DB::transaction(function () use ($product) {

            $paths = $product->images()->pluck('image_path')->toArray();

            $this->deleteFiles($paths);

            $product->images()->delete();

            return $this->repo->delete($product);
        });
    }

    /**
     * Get Product List
     */
    public function list()
    {
        return $this->repo->all();
    }
}