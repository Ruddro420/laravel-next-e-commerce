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
