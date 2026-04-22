<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\PaymentController;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |-------------------------
    | Auth Protected Routes
    |-------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    /*
    |-------------------------
    | Product Routes
    |-------------------------
    */
    Route::apiResource('products', ProductController::class);

    /*
    |-------------------------
    | Cart Routes
    |-------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::post('/update', [CartController::class, 'update']);
        Route::post('/remove', [CartController::class, 'remove']);
    });

    /*
    |-------------------------
    | Checkout Route
    |-------------------------
    */
    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    /*
    |-------------------------
    | Payment Routes
    |-------------------------
    */
    Route::prefix('payment')->group(function () {
        // Route::post('/pay', [PaymentController::class, 'pay']);       // initiate payment
        // Route::post('/success', [PaymentController::class, 'success']); // success callback
        // Route::post('/failed', [PaymentController::class, 'failed']);   // failed callback
    });
});