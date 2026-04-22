<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;

Route::middleware('auth:sanctum')->group(function () {
    // API resource route for products
    Route::apiResource('products', ProductController::class);
});