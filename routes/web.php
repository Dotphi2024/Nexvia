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
})->name('login');

Route::get('/customer/login', function () {
    return redirect()->route('login');
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

// Authorised Delivery & Service Partner (DSP) Public Application Routes
Route::get('/dsp/apply', [\App\Http\Controllers\DspPublicController::class, 'create'])->name('dsp.apply');
Route::post('/dsp/apply', [\App\Http\Controllers\DspPublicController::class, 'store'])->name('dsp.apply.post');
Route::get('/dsp/success', [\App\Http\Controllers\DspPublicController::class, 'success'])->name('dsp.success');
Route::get('/dsp/available-by-pincode', [\App\Http\Controllers\DspPublicController::class, 'availableByPincode'])->name('dsp.available.pincode');

// Dedicated DSP Partner Portal Authentication Routes
Route::get('/dsp/login', [\App\Http\Controllers\Dsp\DspAuthController::class, 'showLoginForm'])->name('dsp.login');
Route::post('/dsp/login', [\App\Http\Controllers\Dsp\DspAuthController::class, 'login'])->name('dsp.login.post');
Route::match(['get', 'post'], '/dsp/logout', [\App\Http\Controllers\Dsp\DspAuthController::class, 'logout'])->name('dsp.logout');

// Dedicated DSP Partner Authenticated Portal
Route::prefix('dsp')->middleware('auth:dsp')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'dashboard'])->name('dsp.dashboard');
    Route::get('/deliveries', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'deliveries'])->name('dsp.deliveries');
    Route::get('/deliveries/{id}', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'deliveryDetail'])->name('dsp.deliveries.show');
    Route::post('/deliveries/{id}/status', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'updateDeliveryStatus'])->name('dsp.deliveries.status');
    Route::get('/wallet', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'wallet'])->name('dsp.wallet');
    Route::post('/wallet/redeem', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'requestPayout'])->name('dsp.wallet.redeem');
    Route::get('/profile', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'profile'])->name('dsp.profile');
    Route::post('/profile/update', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'updateProfile'])->name('dsp.profile.update');
    // DSP Service & Problem Requests
    Route::get('/service-requests', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'serviceRequests'])->name('dsp.service_requests.index');
    Route::get('/service-requests/{id}', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'serviceRequestDetail'])->name('dsp.service_requests.show');
    Route::post('/service-requests/{id}/status', [\App\Http\Controllers\Dsp\DspDashboardController::class, 'updateServiceRequestStatus'])->name('dsp.service_requests.status');
});

// Booking Checkout & Receipt Web Routes
Route::get('/checkout/{slug}', [\App\Http\Controllers\Frontend\BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/checkout/{slug}', [\App\Http\Controllers\Frontend\BookingController::class, 'processCheckout'])->name('booking.process');
Route::get('/booking/receipt/{bookingNumber}', [\App\Http\Controllers\Frontend\BookingController::class, 'receipt'])->name('booking.receipt');
Route::post('/booking/pay-balance/{bookingNumber}', [\App\Http\Controllers\Frontend\BookingController::class, 'payBalance'])->name('booking.pay.balance');
Route::post('/booking/reallocate/{bookingNumber}', [\App\Http\Controllers\Frontend\BookingController::class, 'reallocateToAnotherItem'])->name('booking.reallocate');
Route::post('/booking/select-dsp/{bookingNumber}', [\App\Http\Controllers\Frontend\BookingController::class, 'selectDsp'])->name('booking.select.dsp');
Route::get('/customer/dashboard', [\App\Http\Controllers\Frontend\CustomerDashboardController::class, 'index'])->name('customer.dashboard');
Route::post('/customer/profile/update', [\App\Http\Controllers\Frontend\CustomerDashboardController::class, 'profileUpdate'])->name('customer.profile.update');

// Automated Deployment Webhook Route (matches https://backend.nexviadls.com/deploy.php?key=...)
Route::match(['get', 'post'], '/deploy.php', function (\Illuminate\Http\Request $request) {
    $secret = 'MySecretKey123!';
    if ($request->query('key') !== $secret && $request->input('key') !== $secret) {
        return response('Unauthorized access', 403);
    }
    $projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : base_path();
    @exec('git config --global --add safe.directory ' . escapeshellarg($projectDir));
    @exec('git config --global --add safe.directory "*"');
    $phpBin = defined('PHP_BINARY') && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';
    $command = "cd " . escapeshellarg($projectDir) . " && git pull origin main 2>&1 && {$phpBin} artisan migrate --force 2>&1 && {$phpBin} artisan config:clear 2>&1 && {$phpBin} artisan cache:clear 2>&1";
    $output = shell_exec($command);
    $method = $request->method();
    return response("<pre>Deployment Triggered ($method):\n$output</pre>", 200)
        ->header('Content-Type', 'text/html');
});

Route::match(['get', 'post'], '/deploy', function (\Illuminate\Http\Request $request) {
    $secret = 'MySecretKey123!';
    if ($request->query('key') !== $secret && $request->input('key') !== $secret) {
        return response('Unauthorized access', 403);
    }
    $projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : base_path();
    @exec('git config --global --add safe.directory ' . escapeshellarg($projectDir));
    @exec('git config --global --add safe.directory "*"');
    $phpBin = defined('PHP_BINARY') && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';
    $command = "cd " . escapeshellarg($projectDir) . " && git pull origin main 2>&1 && {$phpBin} artisan migrate --force 2>&1 && {$phpBin} artisan config:clear 2>&1 && {$phpBin} artisan cache:clear 2>&1";
    $output = shell_exec($command);
    $method = $request->method();
    return response("<pre>Deployment Triggered ($method):\n$output</pre>", 200)
        ->header('Content-Type', 'text/html');
});

// Direct product slug/id lookup fallback (e.g. /nexvia-55-inch-ultra-hd-4k-smart-led-tv)
Route::get('/{slugOrId}', function ($slugOrId) {
    if (!in_array($slugOrId, ['admin', 'api', 'login', 'register', 'dashboard', 'search', 'products', 'trending', 'pages', 'dsp'])) {
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
