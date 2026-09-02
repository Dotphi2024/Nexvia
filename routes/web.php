<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\BookingTransferController;
use App\Http\Controllers\Frontend\CustomerDashboardController;
use App\Http\Controllers\Frontend\CustomerAuthController;

// Customer Front-End Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Products Catalog & Detail
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');

// Customer Auth Routes
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp'])->name('customer.send.otp');
Route::get('/verify-otp', [CustomerAuthController::class, 'showVerifyOtp'])->name('customer.verify.otp.view');
Route::post('/verify-otp', [CustomerAuthController::class, 'processVerifyOtp'])->name('customer.verify.otp');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Authenticated Customer Routes
Route::middleware(['auth:web'])->group(function () {
    Route::get('/checkout/{slug}', [BookingController::class, 'checkout'])->name('booking.checkout');
    Route::post('/checkout/{slug}', [BookingController::class, 'processCheckout'])->name('booking.process');
    Route::get('/receipt/{bookingNumber}', [BookingController::class, 'receipt'])->name('booking.receipt');
    Route::post('/pay-balance/{bookingNumber}', [BookingController::class, 'payBalance'])->name('booking.pay.balance');

    // Transferable Booking Routes
    Route::post('/transfer-booking/{bookingNumber}', [BookingTransferController::class, 'initiateTransfer'])->name('booking.transfer.initiate');
    Route::get('/transfer-confirm/{transferId}', [BookingTransferController::class, 'confirmTransferView'])->name('booking.transfer.confirm');
    Route::post('/transfer-confirm/{transferId}', [BookingTransferController::class, 'processTransferConfirmation'])->name('booking.transfer.process');

    // Customer Dashboard
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::post('/profile-update', [CustomerDashboardController::class, 'profileUpdate'])->name('customer.profile.update');
});

require __DIR__ . '/adminauth.php';
require __DIR__ . '/admin.php';
