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

Route::get('/pages/{slug}', function ($slug) {
    $page = \App\Models\Page::active()->where('slug', $slug)->firstOrFail();
    return view('frontend.pages.show', compact('page'));
})->name('pages.show');

Route::get('/privacy-policy', function () {
    $page = \App\Models\Page::active()->where('slug', 'privacy-policy')->firstOrFail();
    return view('frontend.pages.show', compact('page'));
})->name('privacy.policy');

Route::get('/terms-and-conditions', function () {
    $page = \App\Models\Page::active()->where('slug', 'terms-and-conditions')->firstOrFail();
    return view('frontend.pages.show', compact('page'));
})->name('terms.conditions');

// Direct product slug/id lookup fallback (e.g. /nexvia-55-inch-ultra-hd-4k-smart-led-tv)
Route::get('/{slugOrId}', function ($slugOrId) {
    if (!in_array($slugOrId, ['admin', 'api', 'login', 'register', 'dashboard', 'search', 'products', 'trending', 'pages'])) {
        $page = \App\Models\Page::active()->where('slug', $slugOrId)->first();
        if ($page) {
            return view('frontend.pages.show', compact('page'));
        }

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
