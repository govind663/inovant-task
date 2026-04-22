<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\OrderController;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |-------------------------
    | Auth Protected
    |-------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    /*
    |-------------------------
    | Products
    |-------------------------
    */
    Route::apiResource('products', ProductController::class);

    /*
    |-------------------------
    | Cart
    |-------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);       // Get cart
        Route::post('/add', [CartController::class, 'add']);     // Add item
        Route::post('/update', [CartController::class, 'update']);// Update qty
        Route::delete('/remove', [CartController::class, 'remove']); // Remove item ✅ REST fix
    });

    /*
    |-------------------------
    | Checkout
    |-------------------------
    */
    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    /*
    |-------------------------
    | Orders
    |-------------------------
    */
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']); 
        Route::get('/{id}', [OrderController::class, 'show']); 
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    });

    /*
    |-------------------------
    | Payment
    |-------------------------
    */
    Route::prefix('payment')->group(function () {
        Route::post('/pay', [PaymentController::class, 'pay']);
        Route::post('/success', [PaymentController::class, 'success']);
        Route::post('/failed', [PaymentController::class, 'failed']);
    });
});