<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;

Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

// Categories & Products Public APIs
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/products',   [ProductApiController::class, 'index']);
Route::post('/products',  [ProductApiController::class, 'index']);
Route::get('/products/{idOrSlug}', [ProductApiController::class, 'show']);

// Auth Routes (/api/auth/*)
Route::prefix('auth')->group(function () {
    Route::post('/register',        [CustomerAuthController::class, 'register']);
    Route::post('/login',           [CustomerAuthController::class, 'login']);
    Route::post('/send-otp',        [CustomerAuthController::class, 'sendOtp']);
    Route::post('/verify-otp',      [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/refresh-token',   [CustomerAuthController::class, 'refreshToken']);
    Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
    Route::post('/resend-otp',      [CustomerAuthController::class, 'resendOtp']);
    Route::post('/logout',          [CustomerAuthController::class, 'logout']);
    Route::get('/categories',       [CategoryApiController::class, 'index']);
    Route::get('/products',         [ProductApiController::class, 'index']);
    Route::post('/products',        [ProductApiController::class, 'index']);
});

// User Profile & Address Routes (/api/user/*)
Route::prefix('user')->middleware('customer.auth')->group(function () {
    Route::get('/profile',           [CustomerAuthController::class, 'profile']);
    Route::put('/profile',           [CustomerAuthController::class, 'updateProfile']);
    Route::post('/profile',          [CustomerAuthController::class, 'profile']);
    Route::post('/update-profile',   [CustomerAuthController::class, 'updateProfile']);

    // Address Routes
    Route::get('/addresses',         [UserAddressController::class, 'index']);
    Route::post('/addresses',        [UserAddressController::class, 'store']);
    Route::delete('/addresses/{id?}',[UserAddressController::class, 'destroy']);
    Route::post('/addresses/delete', [UserAddressController::class, 'destroy']);
    Route::post('/addresses/remove', [UserAddressController::class, 'destroy']);
});

// Customer Routes (/api/customer/*)
Route::prefix('customer')->group(function () {
    Route::get('/categories',          [CategoryApiController::class, 'index']);
    Route::get('/products',            [ProductApiController::class, 'index']);
    Route::post('/products',           [ProductApiController::class, 'index']);
    Route::get('/products/{idOrSlug}', [ProductApiController::class, 'show']);
    Route::post('/register',           [CustomerAuthController::class, 'register']);
    Route::post('/login',              [CustomerAuthController::class, 'login']);
    Route::post('/send-otp',           [CustomerAuthController::class, 'sendOtp']);
    Route::post('/verify-otp',         [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/refresh-token',      [CustomerAuthController::class, 'refreshToken']);
    Route::post('/forgot-password',    [CustomerAuthController::class, 'forgotPassword']);
    Route::post('/resend-otp',         [CustomerAuthController::class, 'resendOtp']);
    Route::post('/logout',             [CustomerAuthController::class, 'logout']);

    Route::middleware('customer.auth')->group(function () {
        Route::get('/profile',          [CustomerAuthController::class, 'profile']);
        Route::put('/profile',          [CustomerAuthController::class, 'updateProfile']);
        Route::post('/profile',         [CustomerAuthController::class, 'profile']);
        Route::post('/update-profile',  [CustomerAuthController::class, 'updateProfile']);

        // Address Routes
        Route::get('/addresses',        [UserAddressController::class, 'index']);
        Route::post('/addresses',       [UserAddressController::class, 'store']);
        Route::delete('/addresses/{id?}',[UserAddressController::class, 'destroy']);
        Route::post('/addresses/delete', [UserAddressController::class, 'destroy']);
        Route::post('/addresses/remove', [UserAddressController::class, 'destroy']);
    });
});
