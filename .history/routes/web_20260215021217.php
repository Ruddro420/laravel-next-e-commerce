<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Dashboard
Route::view('/dashboard','pages.dashboard')->name('dashboard');

// Product group
Route::view('/products','pages.products.index')->name('products.index');
Route::view('/products/create','pages.products.create')->name('products.create');
Route::view('/products/categories','pages.products.categories')->name('products.categories');
Route::view('/products/brands','pages.products.brands')->name('products.brands');
Route::view('/products/attributes','pages.products.attributes')->name('products.attributes');
Route::view('/products/reviews','pages.products.reviews')->name('products.reviews');

// CRM group
Route::view('/crm/orders','pages.crm.orders')->name('crm.orders');
Route::view('/crm/customers','pages.crm.customers')->name('crm.customers');
Route::view('/crm/coupons','pages.crm.coupons')->name('crm.coupons');
Route::view('/crm/reports','pages.crm.reports')->name('crm.reports');
Route::view('/crm/payments','pages.crm.payments')->name('crm.payments');
Route::view('/crm/taxes','pages.crm.taxes')->name('crm.taxes');
Route::view('/crm/stock','pages.crm.stock')->name('crm.stock');
Route::view('/crm/settings','pages.crm.settings')->name('crm.settings');

// Single pages
Route::view('/analytics','pages.analytics')->name('analytics');
Route::view('/pos','pages.pos')->name('pos');
Route::view('/landing-page','pages.landing')->name('landing');
Route::view('/frontend-design','pages.frontend')->name('frontend');

// Settings group
Route::view('/settings/general','pages.settings.general')->name('settings.general');
Route::view('/settings/users','pages.settings.users')->name('settings.users');
Route::view('/settings/contact','pages.settings.contact')->name('settings.contact');
Route::view('/settings/colors','pages.settings.colors')->name('settings.colors');
Route::view('/settings/fonts','pages.settings.fonts')->name('settings.fonts');
