<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\ClientRegisterController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Middleware\EnsureCustomerIsAuthenticated;
use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\HomeContentController;

Route::middleware('auth')->group(function () {
    Route::get('admin/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
    Route::resource('admin/categories', CategoryController::class)
        ->names('categories');
    Route::resource('admin/brands', BrandController::class)
        ->names('brands');
    Route::resource('admin/products', ProductController::class)
        ->names('products');
    Route::resource('admin/employees', EmployeeController::class)
        ->names('employees');
    Route::post('admin/logout', [AuthenticateController::class, 'destroy'])->name('admin.logout');

    Route::get('admin/customers', [CustomerController::class, 'index'])
        ->name('customers');

    Route::get('admin/transactions', [OrderController::class, 'index'])
        ->name('transactions');
    // Route::put('admin/transactions/{order}/status', [OrderController::class, 'updateStatus'])
    //     ->name('transactions.update-status');
    Route::get('admin/orders', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::get('/admin/customers/{id}/orders', [CustomerController::class, 'showOrders'])->name('customers.orders');
    Route::put('/orders/{order}/approve', [CustomerController::class, 'updateOrderStatus'])
    ->name('orders.update-status');

    // Promotions Routes
    Route::resource('admin/promotions', PromotionController::class)
        ->names('promotions');

    // Home Content Routes
    Route::resource('admin/home-content', HomeContentController::class)
        ->names('home-content');

});

Route::middleware(['guest'])->group(function () {
    Route::get('/admin/login', [AuthenticateController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticateController::class, 'store']);
    // Route::resource('admin/employees', EmployeeController::class)
    //     ->names('employees');
});

require __DIR__ . '/customer-auth.php';



