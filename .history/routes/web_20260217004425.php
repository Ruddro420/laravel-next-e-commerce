<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\POSController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\AnalyticsController;

use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\UserManagementController;
use App\Http\Controllers\Settings\RoleController;


/*
|--------------------------------------------------------------------------
| Public (Guest)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Logout (Auth)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| Protected App (Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Default redirect
    Route::redirect('/', '/dashboard');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::middleware('perm:products.view')->group(function () {

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');

        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Categories
        Route::middleware('perm:products.categories')->group(function () {
            Route::get('/products/categories', [CategoryController::class, 'index'])->name('products.categories');
            Route::post('/products/categories', [CategoryController::class, 'store'])->name('products.categories.store');
            Route::put('/products/categories/{category}', [CategoryController::class, 'update'])->name('products.categories.update');
            Route::delete('/products/categories/{category}', [CategoryController::class, 'destroy'])->name('products.categories.destroy');
        });

        // Brands
        Route::middleware('perm:products.brands')->group(function () {
            Route::get('/products/brands', [BrandController::class, 'index'])->name('products.brands');
            Route::post('/products/brands', [BrandController::class, 'store'])->name('products.brands.store');
            Route::put('/products/brands/{brand}', [BrandController::class, 'update'])->name('products.brands.update');
            Route::delete('/products/brands/{brand}', [BrandController::class, 'destroy'])->name('products.brands.destroy');
        });

        // Attributes
        Route::middleware('perm:products.attributes')->group(function () {
            Route::get('/products/attributes', [AttributeController::class, 'index'])->name('products.attributes');
            Route::post('/products/attributes', [AttributeController::class, 'store'])->name('products.attributes.store');
            Route::put('/products/attributes/{attribute}', [AttributeController::class, 'update'])->name('products.attributes.update');
            Route::delete('/products/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('products.attributes.destroy');
        });

        // Reviews
        Route::middleware('perm:products.reviews')->group(function () {
            Route::get('/products/reviews', [ReviewController::class, 'index'])->name('products.reviews');
            Route::post('/products/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');
            Route::put('/products/reviews/{review}', [ReviewController::class, 'update'])->name('products.reviews.update');
            Route::delete('/products/reviews/{review}', [ReviewController::class, 'destroy'])->name('products.reviews.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | CRM
    |--------------------------------------------------------------------------
    */
    Route::middleware('perm:crm.view')->group(function () {

        // Customers
        Route::middleware('perm:crm.customers')->group(function () {
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
            Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        });

        // Coupons
        Route::middleware('perm:crm.coupons')->group(function () {
            Route::get('/coupons', [CouponController::class, 'index'])->name('crm.coupons');
            Route::post('/crm/coupons', [CouponController::class, 'store'])->name('crm.coupons.store');
            Route::put('/crm/coupons/{coupon}', [CouponController::class, 'update'])->name('crm.coupons.update');
            Route::delete('/crm/coupons/{coupon}', [CouponController::class, 'destroy'])->name('crm.coupons.destroy');
        });

        // Taxes
        Route::middleware('perm:crm.taxes')->group(function () {
            Route::get('/taxes', [TaxRateController::class, 'index'])->name('crm.taxes');
            Route::post('/crm/taxes', [TaxRateController::class, 'store'])->name('crm.taxes.store');
            Route::put('/crm/taxes/{taxRate}', [TaxRateController::class, 'update'])->name('crm.taxes.update');
            Route::delete('/crm/taxes/{taxRate}', [TaxRateController::class, 'destroy'])->name('crm.taxes.destroy');
        });

        // Orders
        Route::middleware('perm:crm.orders')->group(function () {
            Route::get('/orders', [OrderController::class, 'index'])->name('crm.orders');
            Route::get('/crm/orders/create', [OrderController::class, 'create'])->name('crm.orders.create');
            Route::post('/crm/orders', [OrderController::class, 'store'])->name('crm.orders.store');
            Route::get('/crm/orders/{order}', [OrderController::class, 'show'])->name('crm.orders.show');
            Route::get('/crm/orders/{order}/edit', [OrderController::class, 'edit'])->name('crm.orders.edit');
            Route::put('/crm/orders/{order}', [OrderController::class, 'update'])->name('crm.orders.update');
            Route::delete('/crm/orders/{order}', [OrderController::class, 'destroy'])->name('crm.orders.destroy');

            Route::get('/crm/orders/{order}/payment', [PaymentController::class, 'edit'])->name('crm.orders.payment');
            Route::put('/crm/orders/{order}/payment', [PaymentController::class, 'update'])->name('crm.orders.payment.update');
        });

        // Stock
        Route::middleware('perm:crm.stock')->group(function () {
            Route::get('/stock', [StockController::class, 'index'])->name('stock');
            Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');
            Route::post('/stock/{product}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | POS
    |--------------------------------------------------------------------------
    */
    Route::prefix('pos')->middleware('perm:pos.use')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('pos');
        Route::get('/products', [POSController::class, 'products'])->name('pos.products');
        Route::post('/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
        Route::post('/customers', [POSController::class, 'storeCustomer'])->name('pos.customers.store');

        Route::get('/holds', [POSController::class, 'holds'])->name('pos.holds');
        Route::post('/holds', [POSController::class, 'storeHold'])->name('pos.holds.store');
        Route::get('/holds/{hold}', [POSController::class, 'showHold'])->name('pos.holds.show');
        Route::delete('/holds/{hold}', [POSController::class, 'deleteHold'])->name('pos.holds.delete');

        Route::get('/receipt/{order}/a4', [POSController::class, 'receiptA4'])->name('pos.receipt.a4');
        Route::get('/receipt/{order}/58', [POSController::class, 'receipt58'])->name('pos.receipt.58');
        Route::get('/receipt/{order}/80', [POSController::class, 'receipt80'])->name('pos.receipt.80');

        Route::get('/barcode-labels', [POSController::class, 'barcodeLabels'])->name('pos.barcode.labels');
        Route::get('/barcode-products', [POSController::class, 'barcodeProducts'])->name('pos.barcode.products');
        Route::post('/barcode-labels/print', [POSController::class, 'barcodeLabelsPrint'])->name('pos.barcode.labels.print');
    });

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */
    Route::middleware('perm:analytics.view')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analytics/export/csv', [AnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->middleware('perm:settings.view')->group(function () {

        Route::get('/general', [GeneralSettingController::class, 'edit'])->name('settings.general');
        Route::put('/general', [GeneralSettingController::class, 'update'])->name('settings.general.update');

        Route::middleware('perm:settings.users')->group(function () {
            Route::get('/users', [UserManagementController::class, 'index'])->name('settings.users');
            Route::post('/users', [UserManagementController::class, 'store'])->name('settings.users.store');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('settings.users.update');
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('settings.users.destroy');

            Route::get('/roles', [RoleController::class, 'index'])->name('settings.roles');
            Route::post('/roles', [RoleController::class, 'store'])->name('settings.roles.store');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('settings.roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('settings.roles.destroy');
        });
    });
});
