<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
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
        $products = $this->service->list();

        return response()->json([
            'status' => true,
            'message' => 'Product list fetched successfully',
            'data' => ProductResource::collection($products)
        ], 200);
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
            return response()->json([
                'status' => false,
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Single Product
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Product fetched successfully',
            'data' => new ProductResource($product->load('images'))
        ], 200);
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
            return response()->json([
                'status' => false,
                'message' => 'Product update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->service->delete($product);

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}