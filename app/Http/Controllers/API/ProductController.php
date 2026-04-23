<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * List Products
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->service->list();

            // Fix N+1 issue
            $products->getCollection()->load('images');

            return response()->json([
                'status' => true,
                'message' => 'Product list fetched successfully',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Product List Failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch products',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store Product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->service->store($request);

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product)
            ], 201);

        } catch (Exception $e) {
            Log::error('Product Store Failed', [
                'data' => $request->only(['name', 'price']),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Product creation failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show Single Product
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $product->load('images');

            return response()->json([
                'status' => true,
                'message' => 'Product fetched successfully',
                'data' => new ProductResource($product)
            ], 200);

        } catch (Exception $e) {
            Log::error('Product Fetch Failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch product',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update Product
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $product = $this->service->update($request, $product);

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product)
            ], 200);

        } catch (Exception $e) {
            Log::error('Product Update Failed', [
                'product_id' => $product->id,
                'data' => $request->only(['name', 'price']),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Product update failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Delete Product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {

            // Extra safety (optional but pro level)
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $this->service->delete($product);

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (Exception $e) {

            Log::error('Product Delete Failed', [
                'product_id' => $product->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Product deletion failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}