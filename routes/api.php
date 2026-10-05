<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\SubcategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\ReferralWalletApiController;
use App\Http\Controllers\Api\OrderDeliveryApiController;
use App\Http\Controllers\Api\WarrantyAndServiceApiController;
use App\Http\Controllers\Api\SelfDealerApiController;
use App\Http\Controllers\Api\BannerApiController;
use App\Http\Controllers\Api\WishlistApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\CheckoutApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\PageApiController;
use App\Http\Controllers\Api\DspApiController;
use App\Http\Controllers\Api\DlsAgroApiController;

Route::get('/', function () {
    return response()->json(['message' => 'NEXVIA API is running']);
});

// Automated Deployment Webhook API Endpoint
Route::match(['get', 'post'], '/deploy', function (\Illuminate\Http\Request $request) {
    $secret = 'MySecretKey123!';
    if ($request->query('key') !== $secret && $request->input('key') !== $secret) {
        return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
    }
    $projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : base_path();
    @exec('git config --global --add safe.directory ' . escapeshellarg($projectDir));
    @exec('git config --global --add safe.directory "*"');
    $phpBin = defined('PHP_BINARY') && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';
    $command = "cd " . escapeshellarg($projectDir) . " && git pull origin main 2>&1 && {$phpBin} artisan migrate --force 2>&1 && {$phpBin} artisan config:clear 2>&1 && {$phpBin} artisan cache:clear 2>&1";
    $output = shell_exec($command);
    return response()->json([
        'status'  => true,
        'method'  => $request->method(),
        'output'  => $output,
        'time'    => date('Y-m-d H:i:s'),
    ], 200);
});
Route::match(['get', 'post'], '/deploy.php', function (\Illuminate\Http\Request $request) {
    $secret = 'MySecretKey123!';
    if ($request->query('key') !== $secret && $request->input('key') !== $secret) {
        return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
    }
    $projectDir = is_dir('/home/nexviabackend') ? '/home/nexviabackend' : base_path();
    @exec('git config --global --add safe.directory ' . escapeshellarg($projectDir));
    @exec('git config --global --add safe.directory "*"');
    $phpBin = defined('PHP_BINARY') && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';
    $command = "cd " . escapeshellarg($projectDir) . " && git pull origin main 2>&1 && {$phpBin} artisan migrate --force 2>&1 && {$phpBin} artisan config:clear 2>&1 && {$phpBin} artisan cache:clear 2>&1";
    $output = shell_exec($command);
    return response()->json([
        'status'  => true,
        'method'  => $request->method(),
        'output'  => $output,
        'time'    => date('Y-m-d H:i:s'),
    ], 200);
});

// Live Mail Diagnostics & Test Email API Endpoint
Route::match(['get', 'post'], '/test-email', function (\Illuminate\Http\Request $request) {
    $to = trim($request->input('to', $request->query('to', config('mail.from.address', 'nexviadls@gmail.com'))));
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
            'status'  => false,
            'message' => "Invalid recipient email address: '{$to}'",
        ], 422);
    }

    // Optional dynamic overrides for debugging on live
    if ($request->filled('mailer')) {
        config(['mail.default' => $request->input('mailer')]);
    }
    if ($request->filled('host')) {
        config(['mail.mailers.smtp.host' => $request->input('host')]);
    }
    if ($request->filled('port')) {
        config(['mail.mailers.smtp.port' => (int) $request->input('port')]);
    }
    if ($request->filled('encryption')) {
        $enc = $request->input('encryption');
        config(['mail.mailers.smtp.encryption' => ($enc === 'none' || $enc === 'null') ? null : $enc]);
    }

    $currentMailer = config('mail.default');
    $smtpHost      = config('mail.mailers.smtp.host');
    $smtpPort      = (int) config('mail.mailers.smtp.port');
    $smtpEnc       = config('mail.mailers.smtp.encryption');
    $smtpUser      = config('mail.mailers.smtp.username');
    $hasPassword   = !empty(config('mail.mailers.smtp.password'));
    $fromAddress   = config('mail.from.address');
    $fromName      = config('mail.from.name');
    $queueConn     = config('queue.default');

    // Network connectivity diagnostics
    $resolvedIp = null;
    $socket587 = ['connected' => false, 'error' => null];
    $socket465 = ['connected' => false, 'error' => null];
    $socketLocal25 = ['connected' => false, 'error' => null];

    if ($smtpHost) {
        $resolvedIp = @gethostbyname($smtpHost);
    }

    // Test port 587
    $fp = @fsockopen($smtpHost ?: 'smtp.gmail.com', 587, $e, $m, 3);
    if ($fp) {
        $socket587 = ['connected' => true, 'error' => null];
        fclose($fp);
    } else {
        $socket587 = ['connected' => false, 'error' => "{$m} ({$e})"];
    }

    // Test port 465
    $fp = @fsockopen('ssl://' . ($smtpHost ?: 'smtp.gmail.com'), 465, $e, $m, 3);
    if ($fp) {
        $socket465 = ['connected' => true, 'error' => null];
        fclose($fp);
    } else {
        $socket465 = ['connected' => false, 'error' => "{$m} ({$e})"];
    }

    // Test local port 25
    $fp = @fsockopen('127.0.0.1', 25, $e, $m, 2);
    if ($fp) {
        $socketLocal25 = ['connected' => true, 'error' => null];
        fclose($fp);
    } else {
        $socketLocal25 = ['connected' => false, 'error' => "{$m} ({$e})"];
    }

    $t0 = microtime(true);

    try {
        $timestamp = now()->toDateTimeString();
        \Illuminate\Support\Facades\Mail::send('emails.test_diagnostic', [
            'recipient'   => $to,
            'mailer'      => $currentMailer,
            'host'        => $smtpHost,
            'port'        => $smtpPort,
            'encryption'  => $smtpEnc,
            'fromAddress' => $fromAddress,
            'timestamp'   => $timestamp,
        ], function ($m) use ($to, $fromAddress, $fromName, $timestamp) {
            $m->to($to)
              ->from($fromAddress, $fromName)
              ->subject("NEXVIA Service Verification - {$timestamp}");
        });

        $durationMs = round((microtime(true) - $t0) * 1000, 2);

        return response()->json([
            'status'      => true,
            'message'     => "Test email sent successfully to {$to} in {$durationMs}ms!",
            'recipient'   => $to,
            'duration_ms' => $durationMs,
            'config'      => [
                'default_mailer'   => $currentMailer,
                'smtp_host'        => $smtpHost,
                'smtp_port'        => $smtpPort,
                'smtp_encryption'  => $smtpEnc,
                'smtp_username'    => $smtpUser,
                'password_set'     => $hasPassword,
                'from_address'     => $fromAddress,
                'from_name'        => $fromName,
                'queue_connection' => $queueConn,
            ],
            'network_checks' => [
                'resolved_ip'        => $resolvedIp,
                'port_587'           => $socket587,
                'port_465_ssl'       => $socket465,
                'local_port_25_exim' => $socketLocal25,
            ],
            'advice' => 'Email was accepted by the transport driver. Check recipient inbox (and spam/promotions folder).',
        ], 200);

    } catch (\Throwable $e) {
        $durationMs = round((microtime(true) - $t0) * 1000, 2);

        $advice = 'Inspect the error message below.';
        if (str_contains($e->getMessage(), 'Network is unreachable') || str_contains($e->getMessage(), 'Connection refused')) {
            $advice = 'Hosting firewall blocks outbound SMTP connection on this port. Try switching to port 465 (SSL) or use sendmail driver.';
        } elseif (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication')) {
            $advice = 'Authentication failed. Check your MAIL_USERNAME and App Password in .env.';
        }

        return response()->json([
            'status'      => false,
            'message'     => 'Failed to send test email.',
            'error'       => $e->getMessage(),
            'error_type'  => get_class($e),
            'duration_ms' => $durationMs,
            'config'      => [
                'default_mailer'   => $currentMailer,
                'smtp_host'        => $smtpHost,
                'smtp_port'        => $smtpPort,
                'smtp_encryption'  => $smtpEnc,
                'smtp_username'    => $smtpUser,
                'password_set'     => $hasPassword,
                'from_address'     => $fromAddress,
                'from_name'        => $fromName,
                'queue_connection' => $queueConn,
            ],
            'network_checks' => [
                'resolved_ip'        => $resolvedIp,
                'port_587'           => $socket587,
                'port_465_ssl'       => $socket465,
                'local_port_25_exim' => $socketLocal25,
            ],
            'advice' => $advice,
        ], 500);
    }
});

// Privacy Policy & CMS Dynamic Pages Public APIs
Route::match(['get', 'post'], '/privacy-policy',        [PageApiController::class, 'privacyPolicy']);
Route::match(['get', 'post'], '/terms-and-conditions',  [PageApiController::class, 'termsAndConditions']);
Route::match(['get', 'post'], '/terms-conditions',      [PageApiController::class, 'termsAndConditions']);
Route::match(['get', 'post'], '/refund-policy',         [PageApiController::class, 'refundPolicy']);
Route::match(['get', 'post'], '/about-us',              [PageApiController::class, 'aboutUs']);
Route::match(['get', 'post'], '/contact-us',            [PageApiController::class, 'contactUs']);
Route::match(['get', 'post'], '/pages',                 [PageApiController::class, 'index']);
Route::match(['get', 'post'], '/pages/{slugOrId}',      [PageApiController::class, 'show']);

// Categories & Subcategories & Products Public APIs
Route::get('/categories',                          [CategoryApiController::class, 'index']);
Route::post('/categories',                         [CategoryApiController::class, 'index']);
Route::get('/categories/{idOrSlug}',               [CategoryApiController::class, 'show']);
Route::get('/categories/{idOrSlug}/subcategories',  [SubcategoryApiController::class, 'byCategory']);
Route::post('/categories/{idOrSlug}/subcategories', [SubcategoryApiController::class, 'byCategory']);

Route::get('/subcategories',                       [SubcategoryApiController::class, 'index']);
Route::post('/subcategories',                      [SubcategoryApiController::class, 'index']);
Route::get('/subcategories/{idOrSlug}',            [SubcategoryApiController::class, 'show']);
Route::post('/subcategories/{idOrSlug}',           [SubcategoryApiController::class, 'show']);

Route::get('/customer/subcategories',              [SubcategoryApiController::class, 'index']);
Route::post('/customer/subcategories',             [SubcategoryApiController::class, 'index']);
Route::get('/customer/subcategories/{idOrSlug}',   [SubcategoryApiController::class, 'show']);
Route::post('/customer/subcategories/{idOrSlug}',  [SubcategoryApiController::class, 'show']);
Route::get('/products/trending',     [ProductApiController::class, 'trending']);
Route::post('/products/trending',    [ProductApiController::class, 'trending']);
Route::get('/products/search',       [ProductApiController::class, 'search']);
Route::post('/products/search',      [ProductApiController::class, 'search']);
Route::get('/products',              [ProductApiController::class, 'index']);
Route::post('/products',             [ProductApiController::class, 'index']);
Route::get('/products/{idOrSlug}',   [ProductApiController::class, 'show']);
Route::post('/products/{idOrSlug}',  [ProductApiController::class, 'show']);
Route::get('/products/detail/{idOrSlug}', [ProductApiController::class, 'show']);

// DLS Agro & Farm Equipment APIs
Route::match(['get', 'post'], '/dls-agro/categories',                                   [DlsAgroApiController::class, 'categories']);
Route::match(['get', 'post'], '/dls-agro/categories/{idOrSlug}/subcategories',          [DlsAgroApiController::class, 'subcategoriesByCategory']);
Route::match(['get', 'post'], '/dls-agro/subcategories',                                [DlsAgroApiController::class, 'subcategories']);
Route::match(['get', 'post'], '/dls-agro/subcategories/{idOrSlug}',                     [DlsAgroApiController::class, 'subcategoryDetail']);
Route::match(['get', 'post'], '/dls-agro/products',                                     [DlsAgroApiController::class, 'products']);
Route::match(['get', 'post'], '/dls-agro/products/featured',                            [DlsAgroApiController::class, 'featured']);
Route::match(['get', 'post'], '/dls-agro/products/{idOrSlug}',                          [DlsAgroApiController::class, 'show']);

Route::match(['get', 'post'], '/dls-farm/categories',                                   [DlsAgroApiController::class, 'categories']);
Route::match(['get', 'post'], '/dls-farm/categories/{idOrSlug}/subcategories',          [DlsAgroApiController::class, 'subcategoriesByCategory']);
Route::match(['get', 'post'], '/dls-farm/subcategories',                                [DlsAgroApiController::class, 'subcategories']);
Route::match(['get', 'post'], '/dls-farm/subcategories/{idOrSlug}',                     [DlsAgroApiController::class, 'subcategoryDetail']);
Route::match(['get', 'post'], '/dls-farm/products',                                     [DlsAgroApiController::class, 'products']);
Route::match(['get', 'post'], '/dls-farm/products/featured',                            [DlsAgroApiController::class, 'featured']);
Route::match(['get', 'post'], '/dls-farm/products/{idOrSlug}',                          [DlsAgroApiController::class, 'show']);

// Order Delivery Tracking Public API
Route::get('/deliveries/{trackingNumber}', [OrderDeliveryApiController::class, 'trackDelivery']);

// Authorised Delivery & Service Partner (DSP) APIs
// Public DSP Routes
Route::post('/dsp/apply',                      [DspApiController::class, 'store']);
Route::get('/dsp/track/{applicationNumber}',   [DspApiController::class, 'track']);
Route::post('/dsp/login',                      [DspApiController::class, 'login']);
Route::match(['get', 'post'], '/dsp/available-by-pincode', [DspApiController::class, 'availableByPincode']);
Route::match(['get', 'post'], '/dsp/nearby',               [DspApiController::class, 'nearby']);

// Protected DSP Partner APIs (Require Bearer Token or dsp_id via dsp.auth middleware)
Route::middleware(['dsp.auth'])->prefix('dsp')->group(function () {
    Route::post('/logout',                     [DspApiController::class, 'logout']);
    Route::match(['get', 'post'], '/dashboard', [DspApiController::class, 'dashboard']);
    Route::get('/profile',                     [DspApiController::class, 'profile']);
    Route::post('/profile',                    [DspApiController::class, 'updateProfile']);
    Route::match(['get', 'post'], '/deliveries', [DspApiController::class, 'deliveries']);
    Route::match(['get', 'post'], '/deliveries/{id}', [DspApiController::class, 'deliveryDetail']);
    Route::post('/deliveries/{id}/receive',    [DspApiController::class, 'receiveDelivery']);
    Route::post('/deliveries/{id}/verify-otp', [DspApiController::class, 'verifyDeliveryOtp']);
    Route::post('/deliveries/{id}/status',     [DspApiController::class, 'updateDeliveryStatus']);
    Route::match(['get', 'post'], '/wallet',    [DspApiController::class, 'wallet']);
    Route::post('/wallet/redeem',              [DspApiController::class, 'requestPayout']);
    Route::match(['get', 'post'], '/wallet/payout-requests', [DspApiController::class, 'payoutRequests']);

    // DSP Service & Problem Requests Assigned to this Partner
    Route::match(['get', 'post'], '/service-requests',        [DspApiController::class, 'serviceRequests']);
    Route::match(['get', 'post'], '/service-requests/{id}',   [DspApiController::class, 'serviceRequestDetail']);
    Route::post('/service-requests/{id}/status',             [DspApiController::class, 'updateServiceRequestStatus']);
});

// Direct DSP delivery actions
Route::post('/dsp/deliveries/{id}/receive',    [DspApiController::class, 'receiveDelivery']);
Route::post('/dsp/deliveries/{id}/verify-otp', [DspApiController::class, 'verifyDeliveryOtp']);
Route::post('/dsp/deliveries/{id}/status',     [DspApiController::class, 'updateDeliveryStatus']);

// Home Section Banners Public API (Home Index Page Sliders & Promos)
Route::get('/home/banners',         [BannerApiController::class, 'index']);
Route::get('/home-banners',         [BannerApiController::class, 'index']);
Route::get('/banners',              [BannerApiController::class, 'index']);
Route::get('/customer/banners',     [BannerApiController::class, 'index']);
Route::post('/banners/{id}/click',  [BannerApiController::class, 'recordClick']);

// Auth Routes (/api/auth/*)
Route::prefix('auth')->group(function () {
    Route::post('/register',        [CustomerAuthController::class, 'register']);
    Route::post('/login',           [CustomerAuthController::class, 'login']);
    Route::post('/send-otp',        [CustomerAuthController::class, 'sendOtp']);
    Route::post('/verify-otp',      [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/refresh-token',   [CustomerAuthController::class, 'refreshToken']);
    Route::post('/forgot-password', [CustomerAuthController::class, 'forgotPassword']);
    Route::post('/resend-otp',      [CustomerAuthController::class, 'resendOtp']);
    Route::match(['get', 'post'], '/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/categories',       [CategoryApiController::class, 'index']);
    Route::get('/products',         [ProductApiController::class, 'index']);
    Route::post('/products',        [ProductApiController::class, 'index']);
});

Route::match(['get', 'post'], '/logout', [CustomerAuthController::class, 'logout']);

// User Profile & Address Routes (/api/user/*)
Route::prefix('user')->middleware('customer.auth')->group(function () {
    Route::get('/profile',           [CustomerAuthController::class, 'profile']);
    Route::put('/profile',           [CustomerAuthController::class, 'updateProfile']);
    Route::post('/profile',          [CustomerAuthController::class, 'profile']);
    Route::post('/update-profile',   [CustomerAuthController::class, 'updateProfile']);

    // Address Routes
    Route::get('/addresses',         [UserAddressController::class, 'index']);
    Route::post('/addresses',        [UserAddressController::class, 'store']);
    Route::delete('/addresses/{id?}',[UserAddressController::class, 'destroy']);
    Route::post('/addresses/delete', [UserAddressController::class, 'destroy']);
    Route::post('/addresses/remove', [UserAddressController::class, 'destroy']);
});

// Customer Protected Routes (/api/customer/*)
Route::prefix('customer')->group(function () {
    Route::get('/categories',          [CategoryApiController::class, 'index']);
    Route::post('/categories',         [CategoryApiController::class, 'index']);
    Route::get('/categories/{idOrSlug}',[CategoryApiController::class, 'show']);
    Route::get('/products/trending',     [ProductApiController::class, 'trending']);
    Route::post('/products/trending',    [ProductApiController::class, 'trending']);
    Route::get('/products/search',       [ProductApiController::class, 'search']);
    Route::post('/products/search',      [ProductApiController::class, 'search']);
    Route::get('/products',              [ProductApiController::class, 'index']);
    Route::post('/products',             [ProductApiController::class, 'index']);
    Route::get('/products/{idOrSlug}',   [ProductApiController::class, 'show']);
    Route::match(['get', 'post'], '/privacy-policy',        [PageApiController::class, 'privacyPolicy']);
    Route::match(['get', 'post'], '/terms-and-conditions',  [PageApiController::class, 'termsAndConditions']);
    Route::match(['get', 'post'], '/refund-policy',         [PageApiController::class, 'refundPolicy']);
    Route::match(['get', 'post'], '/about-us',              [PageApiController::class, 'aboutUs']);
    Route::match(['get', 'post'], '/contact-us',            [PageApiController::class, 'contactUs']);
    Route::match(['get', 'post'], '/pages',                 [PageApiController::class, 'index']);
    Route::match(['get', 'post'], '/pages/{slugOrId}',      [PageApiController::class, 'show']);
    Route::post('/register',           [CustomerAuthController::class, 'register']);
    Route::post('/login',              [CustomerAuthController::class, 'login']);
    Route::post('/send-otp',           [CustomerAuthController::class, 'sendOtp']);
    Route::post('/verify-otp',         [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/refresh-token',      [CustomerAuthController::class, 'refreshToken']);
    Route::post('/forgot-password',    [CustomerAuthController::class, 'forgotPassword']);
    Route::post('/resend-otp',         [CustomerAuthController::class, 'resendOtp']);
    Route::match(['get', 'post'], '/logout', [CustomerAuthController::class, 'logout']);

    Route::middleware('customer.auth')->group(function () {
        Route::get('/profile',          [CustomerAuthController::class, 'profile']);
        Route::put('/profile',          [CustomerAuthController::class, 'updateProfile']);
        Route::post('/profile',         [CustomerAuthController::class, 'profile']);
        Route::post('/update-profile',  [CustomerAuthController::class, 'updateProfile']);

        // Address Routes
        Route::get('/addresses',        [UserAddressController::class, 'index']);
        Route::post('/addresses',       [UserAddressController::class, 'store']);
        Route::delete('/addresses/{id?}',[UserAddressController::class, 'destroy']);
        Route::post('/addresses/delete', [UserAddressController::class, 'destroy']);
        Route::post('/addresses/remove', [UserAddressController::class, 'destroy']);

        // Bookings & 60-Day Balance Routes
        Route::match(['get', 'post'], '/bookings/list',   [BookingApiController::class, 'index']);
        Route::get('/bookings',                           [BookingApiController::class, 'index']);
        Route::post('/bookings',                          [BookingApiController::class, 'store']);
        Route::get('/bookings/{id}',                      [BookingApiController::class, 'show']);
        Route::match(['get', 'post'], '/bookings/{id}/challan', [BookingApiController::class, 'challan']);
        Route::post('/bookings/{id}/pay-balance',         [BookingApiController::class, 'payBalance']);
        Route::post('/bookings/{id}/reallocate',          [BookingApiController::class, 'reallocate']);
        Route::post('/bookings/{id}/select-dsp',          [BookingApiController::class, 'selectDsp']);
        Route::post('/bookings/{id}/cancel',              [BookingApiController::class, 'cancel']);
        Route::post('/bookings/{id}/transfer',            [BookingApiController::class, 'initiateTransfer']);
        Route::post('/bookings/{id}/transfer/confirm',    [BookingApiController::class, 'confirmTransfer']);

        // Referral & Product Credit Wallet Dashboard & Detailed Ledger Routes
        Route::get('/referral-dashboard',                 [ReferralWalletApiController::class, 'dashboard']);
        Route::match(['get', 'post'], '/referrals',       [ReferralWalletApiController::class, 'referrals']);
        Route::match(['get', 'post'], '/my-referrals',    [ReferralWalletApiController::class, 'referrals']);
        Route::match(['get', 'post'], '/referrals/list',  [ReferralWalletApiController::class, 'referrals']);

        // Multi-item Order Checkout & Delivery Tracking Routes
        Route::post('/orders/checkout',                   [OrderDeliveryApiController::class, 'checkout']);
        Route::get('/deliveries/{trackingNumber}',        [OrderDeliveryApiController::class, 'trackDelivery']);

        // Automatic Warranty & Service Ticket Routes (With Location-Based DSP Allocation & Attended Tracking)
        Route::get('/warranties',                         [WarrantyAndServiceApiController::class, 'warranties']);
        Route::post('/service-tickets',                   [WarrantyAndServiceApiController::class, 'createServiceTicket']);
        Route::get('/service-tickets',                    [WarrantyAndServiceApiController::class, 'listServiceTickets']);
        Route::get('/service-tickets/{id}',               [WarrantyAndServiceApiController::class, 'showServiceTicket']);
        Route::post('/service-requests',                  [WarrantyAndServiceApiController::class, 'createServiceTicket']);
        Route::get('/service-requests',                   [WarrantyAndServiceApiController::class, 'listServiceTickets']);
        Route::get('/service-requests/{id}',              [WarrantyAndServiceApiController::class, 'showServiceTicket']);
        Route::match(['get', 'post'], '/dsp/lookup',      [WarrantyAndServiceApiController::class, 'lookupLocalDsp']);
        Route::post('/installations/schedule',            [WarrantyAndServiceApiController::class, 'scheduleInstallation']);

        // Self Dealer Ecosystem Endpoints (/api/customer/self-dealer/*)
        Route::prefix('self-dealer')->group(function () {
            Route::get('/status',               [SelfDealerApiController::class, 'status']);
            Route::get('/categories',           [SelfDealerApiController::class, 'categories']);
            Route::get('/category/{id}',        [SelfDealerApiController::class, 'categoryDetail']);
            Route::get('/wallet',               [SelfDealerApiController::class, 'wallet']);
            Route::get('/wallet/transactions',  [SelfDealerApiController::class, 'transactions']);
            Route::get('/referrals',            [SelfDealerApiController::class, 'referrals']);
            Route::post('/redeem',              [SelfDealerApiController::class, 'redeem']);
        });
        Route::post('/referral/apply',          [SelfDealerApiController::class, 'applyReferralCode']);

        // Wishlist / Favorites Routes (/api/customer/wishlist)
        Route::match(['get', 'post'], '/wishlist/list', [WishlistApiController::class, 'index']);
        Route::get('/wishlist',                         [WishlistApiController::class, 'index']);
        Route::post('/wishlist',                        [WishlistApiController::class, 'store']);
        Route::post('/wishlist/add',                    [WishlistApiController::class, 'store']);
        Route::post('/wishlist/toggle',                 [WishlistApiController::class, 'toggle']);
        Route::delete('/wishlist/clear',                [WishlistApiController::class, 'clear']);
        Route::post('/wishlist/clear',                  [WishlistApiController::class, 'clear']);
        Route::get('/wishlist/check/{productId}',       [WishlistApiController::class, 'check']);
        Route::delete('/wishlist/{productId?}',         [WishlistApiController::class, 'destroy']);
        Route::post('/wishlist/remove',                 [WishlistApiController::class, 'destroy']);
        Route::post('/wishlist/delete',                 [WishlistApiController::class, 'destroy']);

        // Customer Cart Routes (/api/customer/cart)
        Route::match(['get', 'post'], '/cart/list',      [CartApiController::class, 'index']);
        Route::get('/cart',                              [CartApiController::class, 'index']);
        Route::post('/cart',                             [CartApiController::class, 'store']);
        Route::post('/cart/add',                         [CartApiController::class, 'store']);
        Route::match(['post', 'put'], '/cart/update',    [CartApiController::class, 'update']);
        Route::match(['delete', 'post'], '/cart/remove', [CartApiController::class, 'destroy']);
        Route::match(['delete', 'post'], '/cart/delete', [CartApiController::class, 'destroy']);
        Route::delete('/cart/clear',                     [CartApiController::class, 'clear']);
        Route::post('/cart/clear',                       [CartApiController::class, 'clear']);
        Route::delete('/cart/{id}',                      [CartApiController::class, 'destroy']);

        // Checkout Calculate Route (/api/customer/checkout/calculate)
        Route::post('/checkout/calculate',               [CheckoutApiController::class, 'calculate']);

        // Payment Order & Gateway Routes (/api/customer/payments/*)
        Route::post('/payments/create-order',            [PaymentApiController::class, 'createOrder']);
        Route::post('/payments/verify',                  [PaymentApiController::class, 'verifyPayment']);
    });
});

// Direct Wishlist Routes (/api/wishlist/*)
Route::match(['get', 'post'], '/wishlist/list',         [WishlistApiController::class, 'index']);
Route::get('/wishlist',                                 [WishlistApiController::class, 'index']);
Route::post('/wishlist',                                [WishlistApiController::class, 'store']);
Route::post('/wishlist/add',                            [WishlistApiController::class, 'store']);
Route::post('/wishlist/toggle',                         [WishlistApiController::class, 'toggle']);
Route::delete('/wishlist/clear',                        [WishlistApiController::class, 'clear']);
Route::post('/wishlist/clear',                          [WishlistApiController::class, 'clear']);
Route::get('/wishlist/check/{productId}',               [WishlistApiController::class, 'check']);
Route::delete('/wishlist/{productId?}',                 [WishlistApiController::class, 'destroy']);
Route::post('/wishlist/remove',                         [WishlistApiController::class, 'destroy']);
Route::post('/wishlist/delete',                         [WishlistApiController::class, 'destroy']);

// Direct Cart Routes (/api/cart/*)
Route::match(['get', 'post'], '/cart/list',             [CartApiController::class, 'index']);
Route::get('/cart',                                     [CartApiController::class, 'index']);
Route::post('/cart',                                    [CartApiController::class, 'store']);
Route::post('/cart/add',                                [CartApiController::class, 'store']);
Route::match(['post', 'put'], '/cart/update',           [CartApiController::class, 'update']);
Route::match(['delete', 'post'], '/cart/remove',        [CartApiController::class, 'destroy']);
Route::match(['delete', 'post'], '/cart/delete',        [CartApiController::class, 'destroy']);
Route::delete('/cart/clear',                            [CartApiController::class, 'clear']);
Route::post('/cart/clear',                              [CartApiController::class, 'clear']);
Route::delete('/cart/{id}',                             [CartApiController::class, 'destroy']);

// Direct Checkout Calculate Route (/api/checkout/calculate)
Route::post('/checkout/calculate',                      [CheckoutApiController::class, 'calculate']);

// Direct Payment Order & Gateway Routes (/api/payments/*)
Route::post('/payments/create-order',                   [PaymentApiController::class, 'createOrder']);
Route::post('/payments/verify',                         [PaymentApiController::class, 'verifyPayment']);
Route::post('/payments/webhook',                        [PaymentApiController::class, 'handleWebhook']);
Route::post('/webhooks/razorpay',                       [PaymentApiController::class, 'handleWebhook']);

// Direct Bookings Routes (/api/bookings/*)
Route::match(['get', 'post'], '/bookings/list',         [BookingApiController::class, 'index']);
Route::match(['get', 'post'], '/booking/list',          [BookingApiController::class, 'index']);
Route::post('/bookings',                                [BookingApiController::class, 'store']);
Route::post('/booking',                                 [BookingApiController::class, 'store']);
Route::get('/bookings',                                 [BookingApiController::class, 'index']);
Route::get('/booking',                                  [BookingApiController::class, 'index']);
Route::get('/bookings/{id}',                            [BookingApiController::class, 'show']);
Route::match(['get', 'post'], '/bookings/{id}/challan', [BookingApiController::class, 'challan']);
Route::match(['get', 'post'], '/booking/{id}/challan',  [BookingApiController::class, 'challan']);
Route::post('/bookings/{id}/reallocate',                   [BookingApiController::class, 'reallocate']);
Route::post('/booking/{id}/reallocate',                    [BookingApiController::class, 'reallocate']);
Route::post('/bookings/{id}/select-dsp',                   [BookingApiController::class, 'selectDsp']);
Route::post('/booking/{id}/select-dsp',                    [BookingApiController::class, 'selectDsp']);
Route::post('/bookings/{id}/cancel',                     [BookingApiController::class, 'cancel']);
Route::post('/booking/{id}/cancel',                      [BookingApiController::class, 'cancel']);

// Direct Address Routes (/api/addresses/*)
Route::match(['get', 'post'], '/addresses/list',         [UserAddressController::class, 'index']);
Route::get('/addresses',                                 [UserAddressController::class, 'index']);
Route::post('/addresses',                                [UserAddressController::class, 'store']);
Route::post('/addresses/add',                            [UserAddressController::class, 'store']);
Route::match(['delete', 'post'], '/addresses/remove',    [UserAddressController::class, 'destroy']);
Route::match(['delete', 'post'], '/addresses/delete',    [UserAddressController::class, 'destroy']);
Route::delete('/addresses/{id?}',                        [UserAddressController::class, 'destroy']);

// Direct Referral Routes (/api/referrals, /api/my-referrals)
Route::middleware('customer.auth')->group(function () {
    Route::match(['get', 'post'], '/referrals',    [ReferralWalletApiController::class, 'referrals']);
    Route::match(['get', 'post'], '/my-referrals', [ReferralWalletApiController::class, 'referrals']);
});

// =============================================================================
// V1 SELF DEALER & REFERRAL API SPECIFICATION (DOCUMENTATION SECTION 17)
// =============================================================================
Route::prefix('v1')->group(function () {
    // Public product referral benefit calculator
    Route::get('/products/{id}/referral-benefit', [SelfDealerApiController::class, 'productReferralBenefit']);

    // Authenticated Self Dealer Endpoints
    Route::middleware('customer.auth')->group(function () {
        Route::prefix('self-dealer')->group(function () {
            Route::get('/status',               [SelfDealerApiController::class, 'status']);
            Route::get('/categories',           [SelfDealerApiController::class, 'categories']);
            Route::get('/category/{id}',        [SelfDealerApiController::class, 'categoryDetail']);
            Route::get('/wallet',               [SelfDealerApiController::class, 'wallet']);
            Route::get('/wallet/transactions',  [SelfDealerApiController::class, 'transactions']);
            Route::get('/referrals',            [SelfDealerApiController::class, 'referrals']);
            Route::post('/redeem',              [SelfDealerApiController::class, 'redeem']);
        });

        Route::post('/referral/apply',          [SelfDealerApiController::class, 'applyReferralCode']);
    });
});

