<?php

use Illuminate\Support\Facades\Route;

// Redirect root and any frontend web visits directly to Admin Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

Route::get('/products', function () {
    return redirect()->route('admin.dashboard');
})->name('products.index');

Route::get('/login', function () {
    return redirect()->route('admin.dashboard');
})->name('customer.login');

require __DIR__ . '/adminauth.php';
require __DIR__ . '/admin.php';
