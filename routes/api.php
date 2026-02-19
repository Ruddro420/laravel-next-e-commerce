<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
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
