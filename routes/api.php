<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {

    Route::get('/test', function () {
        return response()->json([
            'message' => 'API is working'
        ]);
    });
});

Route::get('categories', [CategoryController::class, 'getAllCategories']);
Route::get('categories/{id}', [CategoryController::class, 'getCategoryById']);
Route::get('categories/{id}/products', [CategoryController::class, 'getCategoryWiseProducts']);

Route::get('brands', [BrandController::class, 'getAllBrands']);
Route::get('brands/{id}', [BrandController::class, 'getBrandById']);
Route::get('brands/{id}/products', [BrandController::class, 'getBrandWiseProducts']);

// product routes are in ProductController.php
Route::get('products', [ProductController::class, 'apiProducts']);
Route::get('products/{id}', [ProductController::class, 'apiProductShow']);
Route::get('products/{id}/related', [ProductController::class, 'apiRelatedProducts']);
// get cupon
Route::get('coupon/apply', [CouponController::class, 'applyCoupon']);
// order
Route::post('checkout', [OrderController::class, 'apiCheckout']);
// payment
Route::post('orders/{orderId}/payment', [PaymentController::class, 'apiUpdatePayment']);
// need data by order id
Route::get('orders/{id}', [OrderController::class, 'apiGetOrderById']);
// Customer auth routes
Route::prefix('customer-auth')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);

    Route::middleware('auth:customer')->group(function () {
        Route::get('/me', [CustomerAuthController::class, 'me']);
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
    });


    // Route::middleware('auth:customer')->group(function () {
    //     Route::get('/customer/orders', [CustomerOrderController::class, 'index']);
    //     Route::get('/customer/orders/{id}', [CustomerOrderController::class, 'show']);
    // });
    Route::middleware('auth:customer')->group(function () {
        // Route::get('orders/{id}', [OrderController::class, 'apiGetOrderById']);
        // Order routes
        Route::get('/customer/orders', [OrderController::class, 'apiCustomerOrders']);
        Route::get('/customer/orders/{id}', [OrderController::class, 'apiGetOrderById']);
        Route::get('/customer/orders/stats', [OrderController::class, 'apiCustomerOrderStats']);

        // Add other customer routes here
        // Route::get('/customer/profile', [CustomerController::class, 'getProfile']);
        // Route::put('/customer/profile', [CustomerController::class, 'updateProfile']);
    });


    // Route::get('products', [ProductController::class, 'apiProducts']);
});

// Product by brand slug
Route::get('products/brand/{slug}', [ProductController::class, 'apiProductsByBrandSlug']);
