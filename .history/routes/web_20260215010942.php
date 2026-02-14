<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::redirect('/', '/dashboard');

Route::view('/dashboard',  'pages.dashboard')->name('dashboard');
Route::view('/orders',     'pages.orders')->name('orders');
Route::view('/products',   'pages.products')->name('products');
Route::view('/customers',  'pages.customers')->name('customers');
Route::view('/analytics',  'pages.analytics')->name('analytics');
Route::view('/settings',   'pages.settings')->name('settings');
// products
Route::view('/products', 'pages.products.index')->name('products.index');
Route::view('/products/create', 'pages.products.create')->name('products.create');

Route::view('/products/categories', 'pages.products.categories')->name('products.categories');
Route::view('/products/brands', 'pages.products.brands')->name('products.brands');
Route::view('/products/tags', 'pages.products.tags')->name('products.tags');
Route::view('/products/attributes', 'pages.products.attributes')->name('products.attributes');
Route::view('/products/reviews', 'pages.products.reviews')->name('products.reviews');
