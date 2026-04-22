<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductRepository;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductService
{
    use UploadFile;

    protected ProductRepository $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Store Product
     */
    public function store($request): Product
    {
        try {
            return DB::transaction(function () use ($request) {

                $product = $this->repo->create([
                    'name' => $request->name,
                    'price' => $request->price,
                    'user_id' => Auth::id() ?? 1, // fallback (only for testing)
                ]);

                if ($request->hasFile('images')) {
                    $paths = $this->uploadMultiple(
                        $request->file('images'),
                        'products'
                    );

                    $this->saveImages($product->id, $paths);
                }

                return $product->load('images');
            });

        } catch (Exception $e) {
            Log::error('Product Store Failed', [
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            throw $e; // controller handle karega
        }
    }

    /**
     * Update Product
     */
    public function update($request, Product $product): Product
    {
        try {
            return DB::transaction(function () use ($request, $product) {

                // Update basic fields
                $this->repo->update($product, $request->only('name', 'price'));

                /**
                 * Add New Images
                 */
                if ($request->hasFile('images')) {
                    $paths = $this->uploadMultiple(
                        $request->file('images'),
                        'products'
                    );

                    $this->saveImages($product->id, $paths);
                }

                /**
                 * Delete Selected Images
                 */
                if ($request->filled('delete_images')) {

                    $images = $product->images()
                        ->whereIn('id', $request->delete_images)
                        ->get();

                    if ($images->isNotEmpty()) {

                        $this->deleteFiles(
                            $images->pluck('image_path')->toArray()
                        );

                        $product->images()
                            ->whereIn('id', $request->delete_images)
                            ->delete();
                    }
                }

                return $product->load('images');
            });

        } catch (Exception $e) {
            Log::error('Product Update Failed', [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Delete Product
     */
    public function delete(Product $product): bool
    {
        try {
            return DB::transaction(function () use ($product) {

                $this->deleteFiles(
                    $product->images()->pluck('image_path')->toArray()
                );

                $product->images()->delete();

                return $this->repo->delete($product);
            });

        } catch (Exception $e) {
            Log::error('Product Delete Failed', [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get Product List
     */
    public function list(int $perPage = 10)
    {
        return $this->repo->all($perPage);
    }

    /**
     * Save multiple images (helper)
     */
    private function saveImages(int $productId, array $paths): void
    {
        foreach ($paths as $path) {
            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $path,
            ]);
        }
    }
}