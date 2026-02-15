<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\OrderController;


// Route::get('/', function () {
//     return view('welcome');
// });

// Dashboard
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

// Product group
Route::view('/products', 'pages.products.index')->name('products.index');
Route::view('/products/create', 'pages.products.create')->name('products.create');
Route::view('/products/categories', 'pages.products.categories')->name('products.categories');
Route::view('/products/brands', 'pages.products.brands')->name('products.brands');
Route::view('/products/attributes', 'pages.products.attributes')->name('products.attributes');
Route::view('/products/reviews', 'pages.products.reviews')->name('products.reviews');

// Category Controller
Route::get('/products/categories', [CategoryController::class, 'index'])->name('products.categories');
Route::post('/products/categories', [CategoryController::class, 'store'])->name('products.categories.store');
Route::put('/products/categories/{category}', [CategoryController::class, 'update'])->name('products.categories.update');
Route::delete('/products/categories/{category}', [CategoryController::class, 'destroy'])->name('products.categories.destroy');
// Brand Controller
Route::get('/products/brands', [BrandController::class, 'index'])->name('products.brands');
Route::post('/products/brands', [BrandController::class, 'store'])->name('products.brands.store');
Route::put('/products/brands/{brand}', [BrandController::class, 'update'])->name('products.brands.update');
Route::delete('/products/brands/{brand}', [BrandController::class, 'destroy'])->name('products.brands.destroy');
// Review Controller
// Route::get('/products/reviews', [ReviewController::class, 'index'])->name('products.reviews');
// Route::put('/products/reviews/{review}', [ReviewController::class, 'update'])->name('products.reviews.update');
// Route::delete('/products/reviews/{review}', [ReviewController::class, 'destroy'])->name('products.reviews.destroy');


//  Product
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Attribute Controller
Route::get('/products/attributes', [AttributeController::class, 'index'])->name('products.attributes');
Route::post('/products/attributes', [AttributeController::class, 'store'])->name('products.attributes.store');
Route::put('/products/attributes/{attribute}', [AttributeController::class, 'update'])->name('products.attributes.update');
Route::delete('/products/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('products.attributes.destroy');


// Customers
Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// Coupons
Route::get('/coupons', [CouponController::class, 'index'])->name('crm.coupons');
Route::post('/crm/coupons', [CouponController::class, 'store'])->name('crm.coupons.store');
Route::put('/crm/coupons/{coupon}', [CouponController::class, 'update'])->name('crm.coupons.update');
Route::delete('/crm/coupons/{coupon}', [CouponController::class, 'destroy'])->name('crm.coupons.destroy');

// Taxes
Route::get('/taxes', [TaxRateController::class, 'index'])->name('crm.taxes');
Route::post('/crm/taxes', [TaxRateController::class, 'store'])->name('crm.taxes.store');
Route::put('/crm/taxes/{taxRate}', [TaxRateController::class, 'update'])->name('crm.taxes.update');
Route::delete('/crm/taxes/{taxRate}', [TaxRateController::class, 'destroy'])->name('crm.taxes.destroy');

// Orders
Route::get('/crm/orders', [OrderController::class, 'index'])->name('crm.orders');
Route::get('/crm/orders/create', [OrderController::class, 'create'])->name('crm.orders.create');
Route::post('/crm/orders', [OrderController::class, 'store'])->name('crm.orders.store');
Route::get('/crm/orders/{order}', [OrderController::class, 'show'])->name('crm.orders.show');
Route::get('/crm/orders/{order}/edit', [OrderController::class, 'edit'])->name('crm.orders.edit');
Route::put('/crm/orders/{order}', [OrderController::class, 'update'])->name('crm.orders.update');
Route::delete('/crm/orders/{order}', [OrderController::class, 'destroy'])->name('crm.orders.destroy');

Route::get('/crm/orders/{order}/payment', [PaymentController::class,'edit'])->name('crm.orders.payment');
Route::put('/crm/orders/{order}/payment', [PaymentController::class,'update'])->name('crm.orders.payment.update');

// CRM group
Route::view('/crm/orders', 'pages.crm.orders')->name('crm.orders');
Route::view('/crm/customers', 'pages.crm.customers')->name('crm.customers');
Route::view('/crm/coupons', 'pages.crm.coupons')->name('crm.coupons');
Route::view('/crm/reports', 'pages.crm.reports')->name('crm.reports');
Route::view('/crm/payments', 'pages.crm.payments')->name('crm.payments');
Route::view('/crm/taxes', 'pages.crm.taxes')->name('crm.taxes');
Route::view('/crm/stock', 'pages.crm.stock')->name('crm.stock');
Route::view('/crm/settings', 'pages.crm.settings')->name('crm.settings');

// Single pages
Route::view('/analytics', 'pages.analytics')->name('analytics');
Route::view('/pos', 'pages.pos')->name('pos');
Route::view('/landing-page', 'pages.landing')->name('landing');
Route::view('/frontend-design', 'pages.frontend')->name('frontend');

// Settings group
Route::redirect('/settings', '/settings/general')->name('settings');
Route::view('/settings/general', 'pages.settings.general')->name('settings.general');
Route::view('/settings/users', 'pages.settings.users')->name('settings.users');
Route::view('/settings/contact', 'pages.settings.contact')->name('settings.contact');
Route::view('/settings/colors', 'pages.settings.colors')->name('settings.colors');
Route::view('/settings/fonts', 'pages.settings.fonts')->name('settings.fonts');
