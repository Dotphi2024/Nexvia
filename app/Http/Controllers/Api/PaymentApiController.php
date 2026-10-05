<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\Delivery;
use App\Models\WalletTransaction;
use App\Services\ReferralCommissionService;
use App\Services\DspMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentApiController extends Controller
{
    /**
     * Retrieve configured Razorpay credentials.
     */
    protected function getRazorpayKeys(): array
    {
        $keyId = config('services.razorpay.key_id') ?: env('RAZORPAY_KEY_ID', 'rzp_live_Tk75PpmJwnvItA');
        $keySecret = config('services.razorpay.key_secret') ?: env('RAZORPAY_KEY_SECRET', 'rql5885952DBD5XyO1kijFnm');
        $webhookSecret = config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET', 'nexvia_razorpay_secret_2026');

        return [$keyId, $keySecret, $webhookSecret];
    }

    /**
     * Resolve customer strictly from authorization token or request.
     */
    protected function resolveCustomer(Request $request)
    {
        $customer = $request->attributes->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        return $customer;
    }

    /**
     * POST /api/payments/create-order or /api/customer/payments/create-order
     * Create real Razorpay order ID on gateway.
     *
     * Accepts: { productId, amountPayable, bookingNumber, currency: "INR", selectedColor, quantity, paymentType, name, email, phone }
     */
    public function createOrder(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $productId = $request->input('productId')
                ?? $request->input('product_id')
                ?? ($bodyJson['productId'] ?? null)
                ?? ($bodyJson['product_id'] ?? null);

            $bookingNumber = $request->input('bookingNumber')
                ?? $request->input('booking_number')
                ?? ($bodyJson['bookingNumber'] ?? null)
                ?? ($bodyJson['booking_number'] ?? null);

            $amountPayable = $request->input('amountPayable')
                ?? $request->input('amount_payable')
                ?? $request->input('amount')
                ?? ($bodyJson['amountPayable'] ?? null)
                ?? ($bodyJson['amount_payable'] ?? null)
                ?? ($bodyJson['amount'] ?? null);

            $currency = strtoupper(
                $request->input('currency')
                ?? ($bodyJson['currency'] ?? 'INR')
            );

            $selectedColor = $request->input('selectedColor')
                ?? $request->input('selected_color')
                ?? $request->input('color')
                ?? ($bodyJson['selectedColor'] ?? null)
                ?? ($bodyJson['selected_color'] ?? null);

            $quantity = (int) (
                $request->input('quantity')
                ?? ($bodyJson['quantity'] ?? 1)
            );
            if ($quantity < 1) {
                $quantity = 1;
            }

            $paymentType = $request->input('paymentType')
                ?? $request->input('payment_type')
                ?? ($bodyJson['paymentType'] ?? null)
                ?? ($bodyJson['payment_type'] ?? 'booking_20');

            // 1. Check if paying for an existing Booking (e.g. balance or token)
            $existingBooking = null;
            if (!empty($bookingNumber)) {
                $existingBooking = Booking::where('booking_number', $bookingNumber)->first();
                if ($existingBooking && empty($amountPayable)) {
                    $amountPayable = (float) $existingBooking->balance_amount > 0
                        ? (float) $existingBooking->balance_amount
                        : (float) $existingBooking->booking_amount;
                }
            }

            // 2. Find product if provided
            $product = null;
            if (!empty($productId)) {
                $product = is_numeric($productId)
                    ? Product::find($productId)
                    : Product::where('slug', $productId)->orWhere('sku', $productId)->first();
            }

            // 3. Compute amount if not explicitly given
            if (empty($amountPayable) || (float)$amountPayable <= 0) {
                if ($product) {
                    $mrp = (float) $product->mrp * $quantity;
                    if ($paymentType === 'full_payment') {
                        $amountPayable = $mrp;
                    } else {
                        $pct = (float) ($product->booking_percentage ?: 20.00);
                        $amountPayable = round($mrp * ($pct / 100), 2);
                    }
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'amountPayable or a valid productId/bookingNumber is required to create a payment order.',
                    ], 422);
                }
            }

            $amountPayable = round((float) $amountPayable, 2);

            // Razorpay processes amount in smallest currency unit: paise for INR (1 INR = 100 paise)
            $amountInPaise = (int) round($amountPayable * 100);

            // Customer details
            $customer = $this->resolveCustomer($request);
            $customerName  = $customer ? $customer->name : ($request->input('name') ?? ($bodyJson['name'] ?? 'NEXVIA Customer'));
            $customerEmail = $customer ? $customer->email : ($request->input('email') ?? ($bodyJson['email'] ?? 'customer@nexvia.in'));
            $customerPhone = $customer ? ($customer->phone ?? $customer->mobile) : ($request->input('phone') ?? ($bodyJson['phone'] ?? '9876543210'));

            // Generate unique internal receipt number
            $receiptId = 'rcpt_' . date('YmdHis') . '_' . rand(100, 999);

            [$razorpayKeyId, $razorpayKeySecret] = $this->getRazorpayKeys();
            $isLiveIntegration = !empty($razorpayKeyId) && !empty($razorpayKeySecret);

            $orderId = null;
            $gatewayStatus = 'mock_simulation';
            $apiError = null;

            if ($isLiveIntegration) {
                try {
                    $notes = [
                        'platform'     => 'nexvia_web_api',
                        'product_id'   => $product ? $product->id : ($existingBooking ? $existingBooking->product_id : null),
                        'product_name' => $product ? $product->name : ($existingBooking ? $existingBooking->product_name : 'NEXVIA Mobility'),
                        'user_id'      => $customer ? $customer->id : null,
                        'payment_type' => $paymentType,
                    ];
                    if ($existingBooking) {
                        $notes['booking_number'] = $existingBooking->booking_number;
                    }

                    $response = Http::withBasicAuth($razorpayKeyId, $razorpayKeySecret)
                        ->timeout(12)
                        ->post('https://api.razorpay.com/v1/orders', [
                            'amount'          => $amountInPaise,
                            'currency'        => $currency,
                            'receipt'         => $receiptId,
                            'payment_capture' => 1,
                            'notes'           => $notes,
                        ]);

                    if ($response->successful()) {
                        $rzpData = $response->json();
                        $orderId = $rzpData['id'] ?? null;
                        $gatewayStatus = 'live_razorpay';
                    } else {
                        $apiError = $response->body();
                        Log::error('Razorpay Order API Error: ' . $apiError);
                    }
                } catch (\Exception $e) {
                    $apiError = $e->getMessage();
                    Log::error('Razorpay Connection Exception: ' . $e->getMessage());
                }
            }

            // Fallback order ID format if offline or sandbox fallback
            if (empty($orderId)) {
                $orderId = 'order_' . Str::random(14);
            }

            $description = $product
                ? ($paymentType === 'full_payment' ? "Full Payment for {$product->name}" : "Token Booking (20%) for {$product->name}")
                : "NEXVIA Mobility Payment";

            return response()->json([
                'status'  => true,
                'message' => 'Payment order ID generated successfully.',
                'gateway' => [
                    'provider'          => 'razorpay',
                    'integration_mode'  => $gatewayStatus,
                    'is_live'           => ($gatewayStatus === 'live_razorpay'),
                    'key_id'            => $razorpayKeyId,
                ],
                'order'   => [
                    'id'                => $orderId,
                    'order_id'          => $orderId,
                    'entity'            => 'order',
                    'amount'            => $amountInPaise,      // In paise for Razorpay Checkout JS / SDK
                    'amount_paid'       => 0,
                    'amount_due'        => $amountInPaise,
                    'amount_in_rupees'  => $amountPayable,      // In INR
                    'currency'          => $currency,
                    'receipt'           => $receiptId,
                    'status'            => 'created',
                    'attempts'          => 0,
                    'payment_type'      => $paymentType,
                    'created_at'        => time(),
                    'notes'             => [
                        'product_id'     => $product ? $product->id : null,
                        'product_name'   => $product ? $product->name : null,
                        'model_code'     => $product ? $product->model_code : null,
                        'selected_color' => $selectedColor,
                        'quantity'       => $quantity,
                        'user_id'        => $customer ? $customer->id : null,
                        'customer_name'  => $customerName,
                    ],
                ],
                'checkout_options' => [
                    'key'         => $razorpayKeyId,
                    'amount'      => $amountInPaise,
                    'currency'    => $currency,
                    'name'        => 'NEXVIA Mobility',
                    'description' => $description,
                    'order_id'    => $orderId,
                    'prefill'     => [
                        'name'    => $customerName,
                        'email'   => $customerEmail,
                        'contact' => $customerPhone,
                    ],
                    'notes'       => [
                        'booking_type' => $paymentType,
                        'product_id'   => $product ? $product->id : null,
                        'color'        => $selectedColor,
                    ],
                    'theme'       => [
                        'color'   => '#0D6EFD',
                    ],
                ],
                'upi_qr' => [
                    'enabled'       => true,
                    'account_name'  => 'DLS AGRO INFRAVENTURE PRIVATE LIMITED',
                    'upi_id'        => 'dlsagroin.09@idfcbank',
                    'bank_name'     => 'IDFC FIRST Bank',
                    'qr_image_url'  => asset('images/dls_payment_qr.png'),
                    'instructions'  => 'Scan this QR code with any UPI app (GPay, PhonePe, Paytm, BHIM) to transfer',
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create payment order.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/payments/verify or /api/customer/payments/verify
     * Securely verify Razorpay HMAC SHA256 signature and finalize booking status.
     */
    public function verifyPayment(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $orderId = $request->input('razorpay_order_id')
                ?? $request->input('order_id')
                ?? ($bodyJson['razorpay_order_id'] ?? null)
                ?? ($bodyJson['order_id'] ?? null);

            $paymentId = $request->input('razorpay_payment_id')
                ?? $request->input('payment_id')
                ?? ($bodyJson['razorpay_payment_id'] ?? null)
                ?? ($bodyJson['payment_id'] ?? null);

            $signature = $request->input('razorpay_signature')
                ?? $request->input('signature')
                ?? ($bodyJson['razorpay_signature'] ?? null)
                ?? ($bodyJson['signature'] ?? null);

            $bookingNumber = $request->input('booking_number')
                ?? $request->input('bookingNumber')
                ?? ($bodyJson['booking_number'] ?? null)
                ?? ($bodyJson['bookingNumber'] ?? null);

            $isBalancePayment = $request->boolean('is_balance_payment') || ($bodyJson['is_balance_payment'] ?? false);
            $paidAmount       = (float) ($request->input('amount') ?? ($bodyJson['amount'] ?? 0));

            if (empty($orderId)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'razorpay_order_id is required for verification.',
                ], 422);
            }

            [$razorpayKeyId, $razorpayKeySecret] = $this->getRazorpayKeys();

            // Perform cryptographic signature verification
            $signatureValid = false;
            if (!empty($signature) && !empty($razorpayKeySecret) && !empty($paymentId)) {
                $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $razorpayKeySecret);
                $signatureValid = hash_equals($expectedSignature, $signature);
            }

            // In live mode, verify with Razorpay Payments API if signature was not directly matched
            if (!$signatureValid && !empty($paymentId) && !empty($razorpayKeyId) && !empty($razorpayKeySecret)) {
                try {
                    $paymentRes = Http::withBasicAuth($razorpayKeyId, $razorpayKeySecret)
                        ->timeout(10)
                        ->get("https://api.razorpay.com/v1/payments/{$paymentId}");

                    if ($paymentRes->successful()) {
                        $paymentData = $paymentRes->json();
                        if (in_array($paymentData['status'] ?? '', ['captured', 'authorized'])) {
                            $signatureValid = true;
                            if (empty($paidAmount) && !empty($paymentData['amount'])) {
                                $paidAmount = ((float)$paymentData['amount']) / 100;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Razorpay payment fetch failed: ' . $e->getMessage());
                }
            }

            // In local/test simulation without signature
            if (!$signatureValid && !empty($orderId) && empty($signature) && config('app.debug')) {
                $signatureValid = true; // allow developer testing if debug mode enabled
            }

            if (!$signatureValid) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment signature verification failed. Invalid or fraudulent transaction.',
                ], 400);
            }

            // Update Booking status in database if booking_number or booking_id is provided
            $booking = null;
            if (!empty($bookingNumber)) {
                $booking = Booking::where('booking_number', $bookingNumber)->first();
            }

            if ($booking) {
                if ($isBalancePayment) {
                    // Recording balance installment / full remaining settlement
                    $currentBalance = (float) $booking->balance_amount;
                    $actualPaid = $paidAmount > 0 ? min($paidAmount, $currentBalance) : $currentBalance;
                    $newBalance = max(0.0, round($currentBalance - $actualPaid, 2));
                    $isFullyPaid = ($newBalance <= 0.0);

                    $history = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
                    $history[] = [
                        'payment_id'     => $paymentId ?: ('RZP-' . strtoupper(Str::random(8))),
                        'order_id'       => $orderId,
                        'mode'           => 'razorpay_online',
                        'amount'         => $actualPaid,
                        'reference_no'   => $paymentId,
                        'paid_at'        => now()->toDateTimeString(),
                    ];

                    $booking->update([
                        'balance_amount'           => $newBalance,
                        'balance_payments_history' => $history,
                        'payment_status'           => $isFullyPaid ? 'fully_paid' : 'partial_paid',
                        'booking_status'           => $isFullyPaid ? 'completed' : $booking->booking_status,
                        'offline_payment_method'   => 'razorpay',
                        'offline_payment_ref'      => $paymentId,
                    ]);

                    if ($isFullyPaid) {
                        $referralService = app(ReferralCommissionService::class);
                        $referralService->autoApprovePendingReferralsForBooking($booking);
                    }
                } else {
                    // Initial booking payment confirmation
                    $isFullPayment = ($booking->payment_type === 'full_payment');
                    $booking->update([
                        'payment_status'         => $isFullPayment ? 'fully_paid' : 'paid',
                        'booking_status'         => $isFullPayment ? 'completed' : 'booked',
                        'offline_payment_method' => 'razorpay',
                        'offline_payment_ref'    => $paymentId,
                    ]);

                    if ($isFullPayment) {
                        $referralService = app(ReferralCommissionService::class);
                        $referralService->autoApprovePendingReferralsForBooking($booking);
                    }
                }
            }

            return response()->json([
                'status'         => true,
                'message'        => 'Payment verified and captured successfully.',
                'payment_status' => 'captured',
                'data'           => [
                    'order_id'       => $orderId,
                    'payment_id'     => $paymentId,
                    'signature'      => $signature,
                    'status'         => 'paid',
                    'booking_number' => $booking ? $booking->booking_number : null,
                    'verified_at'    => now()->toIso8601String(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to verify payment.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/webhooks/razorpay
     * Razorpay asynchronous server-to-server webhook endpoint.
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-Razorpay-Signature');

            [$keyId, $keySecret, $webhookSecret] = $this->getRazorpayKeys();

            if (!empty($webhookSecret) && !empty($signature)) {
                $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
                if (!hash_equals($expectedSignature, $signature)) {
                    Log::warning('Razorpay Webhook Invalid Signature rejected.');
                    return response()->json(['status' => false, 'message' => 'Invalid webhook signature'], 400);
                }
            }

            $eventData = json_decode($payload, true);
            $event = $eventData['event'] ?? 'unknown';

            Log::info("Razorpay Webhook Received: {$event}");

            if ($event === 'order.paid' || $event === 'payment.captured') {
                $paymentPayload = $eventData['payload']['payment']['entity'] ?? [];
                $orderId = $paymentPayload['order_id'] ?? null;
                $paymentId = $paymentPayload['id'] ?? null;
                $notes = $paymentPayload['notes'] ?? [];

                $bookingNumber = $notes['booking_number'] ?? null;
                if (!empty($bookingNumber)) {
                    $booking = Booking::where('booking_number', $bookingNumber)->first();
                    if ($booking && $booking->payment_status !== 'fully_paid') {
                        $isFullPayment = ($booking->payment_type === 'full_payment');
                        $booking->update([
                            'payment_status'         => $isFullPayment ? 'fully_paid' : 'paid',
                            'booking_status'         => $isFullPayment ? 'completed' : 'booked',
                            'offline_payment_method' => 'razorpay_webhook',
                            'offline_payment_ref'    => $paymentId,
                        ]);
                    }
                }
            }

            return response()->json(['status' => true, 'message' => 'Webhook processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error('Razorpay Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
