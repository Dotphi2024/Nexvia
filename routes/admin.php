<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

// AdminPanel Routes:
Route::prefix('admin')->middleware(['guard.restrict:admin', 'admin.active'])->group(function () {
    // Dashboard Route:
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
});

