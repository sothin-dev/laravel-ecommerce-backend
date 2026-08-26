<?php

use App\Http\Controllers\Admin as Admin;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Auth
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Password reset (customer)
Route::post('/forgot-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendResetLink'])
    ->middleware('throttle:5,1');
Route::post('/reset-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Admin API Routes (Sanctum token auth, "admin" team)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [\App\Http\Controllers\Api\AdminForgotPasswordController::class, 'sendResetLink']);

    Route::middleware('auth:sanctum')->group(function () {
        // Verify the token belongs to an admin
        Route::middleware('admin.token')->group(function () {

            Route::get('/me', [Admin\AuthController::class, 'me']);
            Route::post('/logout', [Admin\AuthController::class, 'logout']);
            Route::get('/profile', [Admin\ProfileController::class, 'show']);
            Route::patch('/profile', [Admin\ProfileController::class, 'update']);
            Route::patch('/profile/password', [Admin\ProfileController::class, 'changePassword']);

            Route::get('/dashboard', [Admin\DashboardController::class, 'index']);
            Route::get('/reports/summary', [Admin\ReportController::class, 'summary']);

            Route::apiResource('categories', Admin\CategoryController::class);
            Route::apiResource('products', Admin\ProductController::class);
            Route::apiResource('orders', Admin\OrderController::class)->only(['index', 'show']);
            Route::post('/orders/{id}/status', [Admin\OrderController::class, 'updateStatus']);
            Route::get('/customers', [Admin\CustomerController::class, 'index']);
            Route::get('/customers/{id}', [Admin\CustomerController::class, 'show']);
            Route::post('/customers/{id}/toggle-status', [Admin\CustomerController::class, 'toggleStatus']);
            Route::apiResource('coupons', Admin\CouponController::class);
            Route::get('/inventory', [Admin\InventoryController::class, 'index']);
            Route::post('/inventory/{id}/stock', [Admin\InventoryController::class, 'adjustStock']);
            Route::get('/reviews', [Admin\ReviewController::class, 'index']);
            Route::post('/reviews/{id}/approve', [Admin\ReviewController::class, 'approve']);
            Route::post('/reviews/{id}/hide', [Admin\ReviewController::class, 'hide']);
            Route::delete('/reviews/{id}', [Admin\ReviewController::class, 'destroy']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Protected API Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::patch('/profile/password', [ProfileController::class, 'changePassword']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{productId}', [WishlistController::class, 'toggle']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::post('/orders/{orderNumber}/reorder', [OrderController::class, 'reorder']);
    Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);

    // Coupons
    Route::post('/coupon/validate', [CouponController::class, 'validate']);

    // Reviews
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
    Route::put('/products/{productId}/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/products/{productId}/reviews/{reviewId}', [ReviewController::class, 'destroy']);
});
