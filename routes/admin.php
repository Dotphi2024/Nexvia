<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\BookingEngineController;
use App\Http\Controllers\Admin\ServiceRequestController;

// AdminPanel Routes
Route::prefix('admin')->middleware(['guard.restrict:admin', 'admin.active'])->group(function () {
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Category Management
    Route::get('/categories', [CategoryAdminController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [CategoryAdminController::class, 'store'])->name('admin.categories.store');
    Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy'])->name('admin.categories.destroy');

    // Product Management
    Route::get('/products', [ProductAdminController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductAdminController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [ProductAdminController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [ProductAdminController::class, 'update'])->name('admin.products.update');
    Route::post('/products/{id}/status', [ProductAdminController::class, 'toggleStatus'])->name('admin.products.status');
    Route::delete('/products/{id}', [ProductAdminController::class, 'destroy'])->name('admin.products.destroy');

    // Bookings & 60-Day Balance Management
    Route::get('/bookings', [BookingAdminController::class, 'index'])->name('admin.bookings.index');
    Route::get('/bookings/{id}', [BookingAdminController::class, 'show'])->name('admin.bookings.show');
    Route::post('/bookings/{id}/status', [BookingAdminController::class, 'updateStatus'])->name('admin.bookings.update.status');
    Route::get('/transfers-audit', [BookingAdminController::class, 'transfers'])->name('admin.transfers.audit');

    // Booking Engine Configuration Controls
    Route::get('/booking-engine', [BookingEngineController::class, 'settings'])->name('admin.booking.engine.settings');
    Route::post('/booking-engine', [BookingEngineController::class, 'updateSettings'])->name('admin.booking.engine.update');

    // Service & Warranty Requests
    Route::get('/service-requests', [ServiceRequestController::class, 'index'])->name('admin.service.requests.index');
    Route::post('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus'])->name('admin.service.requests.status');
});
