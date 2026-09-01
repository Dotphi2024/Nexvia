<?php

use App\Http\Controllers\AuthAdmin\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('admin.login.page');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.submit');
});

Route::prefix('admin')->middleware(['guard.restrict:admin', 'admin.active'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});
