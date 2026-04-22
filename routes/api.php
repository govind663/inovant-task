<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\AdminCartController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// 🔐 Authentication (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Auth: Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |-------------------------
    | Auth
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
        Route::get('/', [CartController::class, 'index']);       
        Route::post('/add', [CartController::class, 'add']);     
        Route::post('/update', [CartController::class, 'update']);
        Route::delete('/remove', [CartController::class, 'remove']);
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


    /*
    |-------------------------
    | CMS / Admin
    |-------------------------
    */
    Route::prefix('admin')->group(function () {

        Route::get('/carts', [AdminCartController::class, 'index']);

        Route::get('/carts/{id}', [AdminCartController::class, 'show']);

        Route::get('/users/{user}/cart', [AdminCartController::class, 'showUserCart']);

    });

});