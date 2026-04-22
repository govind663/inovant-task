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

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |-------------------------
    | Auth
    |-------------------------
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
    });


    /*
    |-------------------------
    | Products
    |-------------------------
    */
    Route::apiResource('products', ProductController::class)
        ->names('products');


    /*
    |-------------------------
    | Cart
    |-------------------------
    */
    Route::prefix('cart')->name('cart.')->group(function () {

        Route::get('/', [CartController::class, 'index'])->name('index');

        Route::post('items', [CartController::class, 'add'])->name('add');

        Route::patch('items/{item_id}', [CartController::class, 'update'])->name('update');

        Route::delete('items/{item_id}', [CartController::class, 'remove'])->name('remove');
    });


    /*
    |-------------------------
    | Checkout
    |-------------------------
    */
    Route::post('checkout', [CheckoutController::class, 'checkout'])
        ->name('checkout');


    /*
    |-------------------------
    | Orders
    |-------------------------
    */
    Route::prefix('orders')->name('orders.')->group(function () {

        Route::get('/', [OrderController::class, 'index'])->name('index');

        Route::get('{id}', [OrderController::class, 'show'])->name('show');

        Route::patch('{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });


    /*
    |-------------------------
    | Payments
    |-------------------------
    */
    Route::prefix('payments')->name('payments.')->group(function () {

        Route::post('/', [PaymentController::class, 'pay'])->name('pay');

        Route::post('success', [PaymentController::class, 'success'])->name('success');

        Route::post('failed', [PaymentController::class, 'failed'])->name('failed');
    });


    /*
    |-------------------------
    | Admin / CMS
    |-------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('carts', [AdminCartController::class, 'index'])->name('carts.index');

        Route::get('carts/{id}', [AdminCartController::class, 'show'])->name('carts.show');

        Route::get('users/{user}/cart', [AdminCartController::class, 'showUserCart'])->name('users.cart');
    });

});