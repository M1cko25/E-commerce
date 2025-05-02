<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MyOrdersController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\ClientLoginController;
use App\Http\Controllers\OrderDetailsController;
use App\Http\Controllers\ClientRegisterController;
use App\Http\Controllers\CompareProductsController;
use App\Http\Controllers\Client\PCBuilderController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Client\ProductListController;
use App\Http\Controllers\Client\ProductViewController;
use App\Http\Middleware\EnsureCustomerIsAuthenticated;
use App\Http\Controllers\Client\ClientProfileController;
use App\Http\Controllers\Client\CategoryProductsController;
use App\Http\Controllers\Client\ComponentSelectionController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProductRatingController;

Route::middleware(['auth:customer'])->group(function () {
    Route::post('/customer/logout', [ClientRegisterController::class, 'destroy'])->name('customer.logout');


    // ---Email Verification -- //
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['auth:customer', 'signed'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    // Manual verification fallback route
    Route::get('/email/verify/{id}/manual-verification', [EmailVerificationController::class, 'manualVerify'])
        ->middleware(['auth:customer'])
        ->name('verification.manual');


    Route::middleware(['verified', EnsureCustomerIsAuthenticated::class])->group(function () {
        Route::get('/customer/register/process', [ClientRegisterController::class, 'show'])
            ->name('customer.register.process');
        Route::post('/customer/register/process', [ClientRegisterController::class, 'update'])
            ->name('customer.register.process.update');

        Route::get('/customer/profile', [ClientProfileController::class, 'index'])
            ->name('customer.profile');

        // Add security routes
        Route::get('/customer/security', [ClientProfileController::class, 'security'])
            ->name('customer.security');
        Route::post('/customer/security/update-password', [ClientProfileController::class, 'updatePassword'])
            ->name('customer.security.update');

        // Customer Address Routes
        Route::get('/customer/addresses', [CustomerAddressController::class, 'index'])
            ->name('customer.addresses');
        Route::post('/customer/addresses', [CustomerAddressController::class, 'store'])
            ->name('customer.addresses.store');
        Route::put('/customer/addresses/{id}', [CustomerAddressController::class, 'update'])
            ->name('customer.addresses.update');
        Route::delete('/customer/addresses/{id}', [CustomerAddressController::class, 'destroy'])
            ->name('customer.addresses.destroy');
        Route::patch('/customer/addresses/{id}/default', [CustomerAddressController::class, 'setDefault'])
            ->name('customer.addresses.default');

        Route::get('/customer/checkout', [CheckoutController::class, 'index'])
            ->name('customer.checkout');
        // Route::post('/customer/checkout', [CheckoutController::class, 'store'])
        //     ->name('customer.checkout.store');

        Route::get('/customer/payment/success', [CheckoutController::class, 'success'])
            ->name('customer.payment.success');
        Route::get('/customer/my-orders', [MyOrdersController::class, 'index'])
            ->name('customer.myOrders');

        // Wishlist Routes
        Route::get('/customer/wishlist', [WishlistController::class, 'index'])
            ->name('customer.wishlist');
        Route::post('/customer/wishlist', [WishlistController::class, 'store'])
            ->name('customer.wishlist.store');
        Route::delete('/customer/wishlist/{id}', [WishlistController::class, 'destroy'])
            ->name('customer.wishlist.destroy');
        Route::post('/customer/wishlist/toggle', [WishlistController::class, 'toggle'])
            ->name('customer.wishlist.toggle');

        // Product Rating Routes
        Route::post('/product/rate', [ProductRatingController::class, 'store'])
            ->name('product.rate');
        Route::get('/product/{productId}/ratings', [ProductRatingController::class, 'getProductRatings'])
            ->name('product.ratings');

        Route::get('payment', [CheckoutController::class, 'pay'])
            ->name('customer.payment');
        Route::post('process-cod', [CheckoutController::class, 'processCod'])
            ->name('customer.processCod');

        // Add QR Payment routes
        Route::get('/customer/qr-payment', [CheckoutController::class, 'showQrPayment'])
            ->name('customer.qrPayment');
        Route::post('/customer/payment/confirm', [CheckoutController::class, 'confirmPayment'])
            ->name('customer.payment.confirm');

        Route::get('/customer/orders/{reference_number}', [OrderDetailsController::class, 'show'])
            ->name('customer.orderDetails');

        Route::get('invoice/download/{reference_number}', [OrderDetailsController::class, 'downloadInvoice'])
            ->name('invoice.download');

        // Return and Refund Routes
        Route::get('/customer/returns/{reference_number}', [CustomerReturnController::class, 'showReturnForm'])
            ->name('customer.returnRequest');
        Route::post('/customer/returns/{reference_number}', [CustomerReturnController::class, 'submitReturnRequest'])
            ->name('customer.submitReturn');
        Route::post('/customer/returns/{reference_number}/cancel', [CustomerReturnController::class, 'cancelReturnRequest'])
            ->name('customer.cancelReturn');

        Route::inertia('/customer/try', 'ClientSide/Customer/Try');


    });
});

Route::middleware(['guest'])->group(function () {
    Route::get('/customer/login', [ClientLoginController::class, 'create'])
        ->name('customer.login');
    Route::post('/customer/login', [ClientLoginController::class, 'store'])
        ->name('customer.login.store');

    Route::get('/customer/register', [ClientRegisterController::class, 'create'])
        ->name('customer.register');
    Route::post('/customer/register', [ClientRegisterController::class, 'store'])
        ->name('customer.register.store');

    Route::get('/', [HomeController::class, 'index'])
        ->name('home'); // Guest Home
    Route::get('/product-list', [ProductListController::class, 'index'])
        ->name('product.list');
    Route::get('/category-products/{categorySlug?}', [CategoryProductsController::class, 'index'])
        ->name('category.products');
    Route::get('/product-list/{slug}', [ProductViewController::class, 'index'])
        ->name('product.view');
    Route::get('/pc-builder', [PCBuilderController::class, 'index'])
    ->name('pc.builder');
    Route::get('/pc-builder/component-selection/{componentType}', [ComponentSelectionController::class, 'index'])
        ->name('component.selection');

    Route::get('/compare/products', [CompareProductsController::class, 'index'])
        ->name('compare.products');





});
