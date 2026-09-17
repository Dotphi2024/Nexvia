<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\BookingEngineController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\BannerAdminController;
use App\Http\Controllers\Admin\PageAdminController;

// AdminPanel Routes
Route::prefix('admin')->middleware(['guard.restrict:admin', 'admin.active'])->group(function () {
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Category Management
    Route::get('/categories', [CategoryAdminController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [CategoryAdminController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{id}', [CategoryAdminController::class, 'update'])->name('admin.categories.update');
    Route::post('/categories/{id}/update', [CategoryAdminController::class, 'update'])->name('admin.categories.update.post');
    Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy'])->name('admin.categories.destroy');

    // Product Management
    Route::get('/products', [ProductAdminController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductAdminController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{id}/edit', [ProductAdminController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{id}', [ProductAdminController::class, 'update'])->name('admin.products.update');
    Route::post('/products/{id}/status', [ProductAdminController::class, 'toggleStatus'])->name('admin.products.status');
    Route::post('/products/{id}/featured', [ProductAdminController::class, 'toggleFeatured'])->name('admin.products.featured');
    Route::delete('/products/{id}', [ProductAdminController::class, 'destroy'])->name('admin.products.destroy');

    // Banner & Slider Management
    Route::get('/banners', [BannerAdminController::class, 'index'])->name('admin.banners.index');
    Route::post('/banners', [BannerAdminController::class, 'store'])->name('admin.banners.store');
    Route::get('/banners/{id}/edit', [BannerAdminController::class, 'edit'])->name('admin.banners.edit');
    Route::put('/banners/{id}', [BannerAdminController::class, 'update'])->name('admin.banners.update');
    Route::post('/banners/{id}/status', [BannerAdminController::class, 'toggleStatus'])->name('admin.banners.status');
    Route::delete('/banners/{id}', [BannerAdminController::class, 'destroy'])->name('admin.banners.destroy');

    // Bookings & 60-Day Balance Management
    Route::get('/bookings', [BookingAdminController::class, 'index'])->name('admin.bookings.index');
    Route::get('/bookings/{id}', [BookingAdminController::class, 'show'])->name('admin.bookings.show');
    Route::post('/bookings/{id}/status', [BookingAdminController::class, 'updateStatus'])->name('admin.bookings.update.status');
    Route::get('/transfers-audit', [BookingAdminController::class, 'transfers'])->name('admin.transfers.audit');
    Route::post('/transfers/{id}/approve', [BookingAdminController::class, 'approveTransfer'])->name('admin.transfers.approve');
    Route::post('/transfers/{id}/reject', [BookingAdminController::class, 'rejectTransfer'])->name('admin.transfers.reject');

    // Booking Engine Configuration Controls
    Route::get('/booking-engine', [BookingEngineController::class, 'settings'])->name('admin.booking.engine.settings');
    Route::post('/booking-engine', [BookingEngineController::class, 'updateSettings'])->name('admin.booking.engine.update');

    // Service & Warranty Requests
    Route::get('/service-requests', [ServiceRequestController::class, 'index'])->name('admin.service.requests.index');
    Route::post('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus'])->name('admin.service.requests.status');

    // Customer Account & Address Management
    Route::get('/customers', [\App\Http\Controllers\Admin\CustomerAdminController::class, 'index'])->name('admin.customers.index');
    Route::get('/customers/{id}', [\App\Http\Controllers\Admin\CustomerAdminController::class, 'show'])->name('admin.customers.show');
    Route::post('/customers/{id}/status', [\App\Http\Controllers\Admin\CustomerAdminController::class, 'updateStatus'])->name('admin.customers.status');
    Route::delete('/customers/{id}', [\App\Http\Controllers\Admin\CustomerAdminController::class, 'destroy'])->name('admin.customers.destroy');

    // =========================================================================
    // SELF DEALER MANAGEMENT
    // =========================================================================
    Route::prefix('self-dealers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'index'])->name('admin.self_dealers.index');
        Route::get('/fraud-flags/list', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'fraudFlags'])->name('admin.self_dealers.fraud_flags');
        Route::post('/fraud-flags/{flagId}/review', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'reviewFraudFlag'])->name('admin.self_dealers.fraud_review');
        Route::get('/transactions/all', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'transactions'])->name('admin.self_dealers.transactions');
        Route::get('/{id}', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'show'])->name('admin.self_dealers.show');
        Route::post('/{id}/status', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'updateStatus'])->name('admin.self_dealers.status');
        Route::post('/referrals/{referralId}/qualify', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'qualifyReferral'])->name('admin.self_dealers.qualify');
        Route::post('/referrals/{referralId}/reverse', [\App\Http\Controllers\Admin\SelfDealerAdminController::class, 'reverseReferral'])->name('admin.self_dealers.reverse');
    });

    // =========================================================================
    // REFERRAL INCENTIVE CONFIGURATION
    // =========================================================================
    Route::get('/referral-config', [\App\Http\Controllers\Admin\ReferralStageConfigController::class, 'settings'])->name('admin.referral.config.settings');
    Route::post('/referral-config', [\App\Http\Controllers\Admin\ReferralStageConfigController::class, 'update'])->name('admin.referral.config.update');

    // =========================================================================
    // CMS PAGES & POLICY MANAGEMENT
    // =========================================================================
    Route::get('/pages', [PageAdminController::class, 'index'])->name('admin.pages.index');
    Route::get('/pages/create', [PageAdminController::class, 'create'])->name('admin.pages.create');
    Route::post('/pages', [PageAdminController::class, 'store'])->name('admin.pages.store');
    Route::get('/pages/{id}/edit', [PageAdminController::class, 'edit'])->name('admin.pages.edit');
    Route::put('/pages/{id}', [PageAdminController::class, 'update'])->name('admin.pages.update');
    Route::post('/pages/{id}/update', [PageAdminController::class, 'update'])->name('admin.pages.update.post');
    Route::post('/pages/{id}/status', [PageAdminController::class, 'toggleStatus'])->name('admin.pages.status');
    Route::delete('/pages/{id}', [PageAdminController::class, 'destroy'])->name('admin.pages.destroy');
});
