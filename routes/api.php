<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\CategoryApiController;
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

Route::get('/', function () {
    return response()->json(['message' => 'NEXVIA API is running']);
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

// Categories & Products Public APIs
Route::get('/categories',           [CategoryApiController::class, 'index']);
Route::post('/categories',          [CategoryApiController::class, 'index']);
Route::get('/categories/{idOrSlug}',[CategoryApiController::class, 'show']);
Route::get('/products/trending',     [ProductApiController::class, 'trending']);
Route::post('/products/trending',    [ProductApiController::class, 'trending']);
Route::get('/products/search',       [ProductApiController::class, 'search']);
Route::post('/products/search',      [ProductApiController::class, 'search']);
Route::get('/products',              [ProductApiController::class, 'index']);
Route::post('/products',             [ProductApiController::class, 'index']);
Route::get('/products/{idOrSlug}',   [ProductApiController::class, 'show']);
Route::post('/products/{idOrSlug}',  [ProductApiController::class, 'show']);
Route::get('/products/detail/{idOrSlug}', [ProductApiController::class, 'show']);

// Order Delivery Tracking Public API
Route::get('/deliveries/{trackingNumber}', [OrderDeliveryApiController::class, 'trackDelivery']);

// Authorised Delivery & Service Partner (DSP) APIs
// Public DSP Routes
Route::post('/dsp/apply',                      [DspApiController::class, 'store']);
Route::get('/dsp/track/{applicationNumber}',   [DspApiController::class, 'track']);
Route::post('/dsp/login',                      [DspApiController::class, 'login']);
Route::match(['get', 'post'], '/dsp/available-by-pincode', [DspApiController::class, 'availableByPincode']);

// Protected DSP Partner APIs (Require Bearer Token or dsp_id via dsp.auth middleware)
Route::middleware(['dsp.auth'])->prefix('dsp')->group(function () {
    Route::post('/logout',                     [DspApiController::class, 'logout']);
    Route::match(['get', 'post'], '/dashboard', [DspApiController::class, 'dashboard']);
    Route::get('/profile',                     [DspApiController::class, 'profile']);
    Route::post('/profile',                    [DspApiController::class, 'updateProfile']);
    Route::match(['get', 'post'], '/deliveries', [DspApiController::class, 'deliveries']);
    Route::match(['get', 'post'], '/deliveries/{id}', [DspApiController::class, 'deliveryDetail']);
    Route::post('/deliveries/{id}/status',     [DspApiController::class, 'updateDeliveryStatus']);
    Route::match(['get', 'post'], '/wallet',    [DspApiController::class, 'wallet']);
    Route::post('/wallet/redeem',              [DspApiController::class, 'requestPayout']);
    Route::match(['get', 'post'], '/wallet/payout-requests', [DspApiController::class, 'payoutRequests']);
});

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
        Route::post('/bookings/{id}/pay-balance',         [BookingApiController::class, 'payBalance']);
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

        // Automatic Warranty & Service Ticket Routes
        Route::get('/warranties',                         [WarrantyAndServiceApiController::class, 'warranties']);
        Route::post('/service-tickets',                   [WarrantyAndServiceApiController::class, 'createServiceTicket']);
        Route::get('/service-tickets',                    [WarrantyAndServiceApiController::class, 'listServiceTickets']);
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

// Direct Bookings Routes (/api/bookings/*)
Route::match(['get', 'post'], '/bookings/list',         [BookingApiController::class, 'index']);
Route::match(['get', 'post'], '/booking/list',          [BookingApiController::class, 'index']);
Route::post('/bookings',                                [BookingApiController::class, 'store']);
Route::post('/booking',                                 [BookingApiController::class, 'store']);
Route::get('/bookings',                                 [BookingApiController::class, 'index']);
Route::get('/booking',                                  [BookingApiController::class, 'index']);
Route::get('/bookings/{id}',                            [BookingApiController::class, 'show']);
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

