<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
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
Route::get('coupon', [CouponController::class, 'getCouponByCode']);
