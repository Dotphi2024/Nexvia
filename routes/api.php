<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;

Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

// Customer Auth Routes
Route::prefix('customer')->group(function () {
    Route::post('/register',       [CustomerAuthController::class, 'register']);
    Route::post('/login',          [CustomerAuthController::class, 'login']);
    Route::post('/verify-otp',     [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/resend-otp',     [CustomerAuthController::class, 'resendOtp']);
    Route::post('/logout',         [CustomerAuthController::class, 'logout']);

    Route::middleware('customer.auth')->group(function () {
        Route::get('/profile/{id?}',   [CustomerAuthController::class, 'profile']);
        Route::post('/profile',        [CustomerAuthController::class, 'profile']);
        Route::post('/update-profile', [CustomerAuthController::class, 'updateProfile']);
    });
});


