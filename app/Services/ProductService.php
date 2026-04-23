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

                $userId = Auth::id() ?? 1;

                $product = $this->repo->create([
                    'name' => $request->name,
                    'price' => $request->price,
                    'user_id' => $userId,
                    'created_by' => $userId,
                ]);

                if ($request->hasFile('images')) {

                    $paths = $this->uploadMultiple(
                        $request->file('images'),
                        'products'
                    );

                    $this->saveImages($product->id, $paths, $userId);
                }

                return $product->load('images');
            });

        } catch (Exception $e) {

            Log::error('Product Store Failed', [
                'user_id' => Auth::id(),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Update Product
     */
    public function update($request, Product $product): Product
    {
        try {
            return DB::transaction(function () use ($request, $product) {

                $userId = Auth::id();

                // Update product
                $data = $request->only('name', 'price');
                $data['updated_by'] = $userId;

                $this->repo->update($product, $data);

                /**
                 * Add New Images
                 */
                if ($request->hasFile('images')) {

                    $paths = $this->uploadMultiple(
                        $request->file('images'),
                        'products'
                    );

                    $this->saveImages($product->id, $paths, $userId);
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

                        // set deleted_by before delete
                        $product->images()
                            ->whereIn('id', $request->delete_images)
                            ->update([
                                'deleted_by' => $userId
                            ]);

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

                $userId = Auth::id();

                $product->load('images');

                if ($product->images->isNotEmpty()) {

                    $paths = $product->images->pluck('image_path')->toArray();

                    $this->deleteFiles($paths);

                    $product->images()->update([
                        'deleted_by' => $userId
                    ]);
                }

                $product->images()->delete();

                // set deleted_by before delete
                $product->deleted_by = $userId;
                $product->save();

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
     * Save multiple images (UPDATED 🔥)
     */
    private function saveImages(int $productId, array $paths, $userId = null): void
    {
        $data = [];

        foreach ($paths as $path) {
            $data[] = [
                'product_id' => $productId,
                'image_path' => $path,
                'created_by' => $userId ?? Auth::id(),
            ];
        }

        ProductImage::insert($data);
    }
}