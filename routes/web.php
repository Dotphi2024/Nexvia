<?php

use Illuminate\Support\Facades\Route;

// Redirect root and any frontend web visits directly to Admin Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

Route::get('/products/{idOrSlug}', [\App\Http\Controllers\Api\ProductApiController::class, 'show']);
Route::get('/products', function () {
    return redirect()->route('admin.dashboard');
})->name('products.index');

Route::get('/login', function () {
    return redirect()->route('admin.dashboard');
})->name('customer.login');

// Direct product slug/id lookup fallback (e.g. /nexvia-55-inch-ultra-hd-4k-smart-led-tv)
Route::get('/{slugOrId}', function ($slugOrId) {
    if (!in_array($slugOrId, ['admin', 'api', 'login', 'register', 'dashboard', 'search', 'products', 'trending'])) {
        $product = \App\Models\Product::where('status', 'active')
            ->where(function ($q) use ($slugOrId) {
                if (is_numeric($slugOrId)) {
                    $q->where('id', $slugOrId);
                } else {
                    $q->where('slug', $slugOrId)
                      ->orWhere('model_code', $slugOrId)
                      ->orWhere('sku', $slugOrId);
                }
            })->first();

        if ($product) {
            return app(\App\Http\Controllers\Api\ProductApiController::class)->show($slugOrId);
        }
    }
    return redirect()->route('admin.dashboard');
});

require __DIR__ . '/adminauth.php';
require __DIR__ . '/admin.php';
