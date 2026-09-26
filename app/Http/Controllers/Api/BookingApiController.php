<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Product;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Models\Referral;
use App\Models\SelfDealerWallet;
use App\Models\DspApplication;
use App\Services\ReferralCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingApiController extends Controller
{
    /**
     * GET /api/customer/bookings
     * List all bookings for authenticated customer.
     */
    /**
     * Resolve customer from auth, request parameters, or customerDetails.
     */
    protected function resolveCustomer(Request $request, $customerDetails = [])
    {
        if (is_string($customerDetails)) {
            $decoded = json_decode($customerDetails, true);
            $customerDetails = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($customerDetails)) {
            $customerDetails = [];
        }

        $customer = $request->user('customer') ?? $request->attributes->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? $request->json('api_token')
                ?? $request->json('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        return $customer;
    }

    /**
     * GET /api/customer/bookings or /api/bookings
     * Retrieve user bookings list with optional filter: ?status=ACTIVE|TRANSFERRED
     */
    public function index(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true);
        if (!is_array($bodyJson)) {
            $bodyJson = [];
        }

        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $rawStatus = $request->query('status')
            ?? $request->input('status')
            ?? ($bodyJson['status'] ?? null)
            ?? 'ALL';
        $statusFilter = strtoupper(trim((string)$rawStatus));

        $query = Booking::where('user_id', $user->id)->with('product');

        if ($statusFilter === 'ACTIVE') {
            // Active Flexi-Bookings: original bookings that are not cancelled
            $query->where('transfer_status', 'original')
                  ->where('booking_status', '!=', 'cancelled');
        } elseif ($statusFilter === 'TRANSFERRED') {
            // Transferred bookings (ownership transferred)
            $query->where('transfer_status', 'transferred');
        } elseif ($statusFilter === 'ACTIVE|TRANSFERRED' || $statusFilter === 'ACTIVE,TRANSFERRED') {
            $query->where(function ($q) {
                $q->where('transfer_status', 'transferred')
                  ->orWhere(function ($sub) {
                      $sub->where('transfer_status', 'original')
                          ->where('booking_status', '!=', 'cancelled');
                  });
            });
        } elseif ($statusFilter === 'COMPLETED' || $statusFilter === 'FULLY_PAID') {
            $query->where(function ($q) {
                $q->where('booking_status', 'completed')
                  ->orWhere('payment_status', 'fully_paid');
            });
        } elseif ($statusFilter === 'CANCELLED') {
            $query->where('booking_status', 'cancelled');
        }

        $bookings = $query->orderBy('id', 'desc')->get();

        $formatted = $bookings->map(function ($booking) {
            $product = $booking->product;
            $imageUrl = $product ? \App\Helpers\ImageHelper::resolve($product->main_image) : null;

            $statusBadge = 'ACTIVE';
            if ($booking->transfer_status === 'transferred') {
                $statusBadge = 'TRANSFERRED';
            } elseif ($booking->booking_status === 'cancelled') {
                $statusBadge = 'CANCELLED';
            } elseif ($booking->payment_status === 'fully_paid' || $booking->booking_status === 'completed') {
                $statusBadge = 'COMPLETED';
            }

            return [
                'id'                      => $booking->id,
                'booking_number'          => $booking->booking_number,
                'product_id'              => $booking->product_id,
                'product_name'            => $booking->product_name,
                'model_code'              => $booking->model_code,
                'selected_color'          => $booking->selected_color,
                'quantity'                => (int)$booking->quantity,
                'image'                   => $imageUrl,
                'imageUrl'                => $imageUrl,
                'main_image'              => $imageUrl,
                'mrp'                     => (float)$booking->mrp,
                'booking_amount'          => (float)$booking->booking_amount,
                'token_amount'            => (float)$booking->booking_amount,
                'balance_amount'          => (float)$booking->balance_amount,
                'booking_date'            => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                'balance_due_date'        => $booking->balance_due_date ? $booking->balance_due_date->format('Y-m-d') : null,
                'days_remaining'          => $booking->days_remaining,
                'is_overdue'              => $booking->is_overdue,
                'status_badge'            => $statusBadge,
                'payment_type'            => $booking->payment_type,
                'payment_status'          => $booking->payment_status,
                'booking_status'          => $booking->booking_status,
                'transfer_status'         => $booking->transfer_status,
                'transfer_eligible'       => $booking->transfer_status === 'original' && $booking->payment_status !== 'fully_paid',
                'non_refundable_accepted' => $booking->non_refundable_accepted,
                'customer_name'           => $booking->customer_name,
                'customer_phone'          => $booking->customer_phone,
                'customer_email'          => $booking->customer_email,
                'shipping_address'        => $booking->shipping_address,
                'qr_code_hash'            => $booking->qr_code_hash,
                'created_at'              => $booking->created_at ? $booking->created_at->toIso8601String() : null,
            ];
        });

        $activeCount = Booking::where('user_id', $user->id)
            ->where('transfer_status', 'original')
            ->where('booking_status', '!=', 'cancelled')
            ->count();

        $transferredCount = Booking::where('user_id', $user->id)
            ->where('transfer_status', 'transferred')
            ->count();

        return response()->json([
            'status'            => true,
            'message'           => 'Bookings retrieved successfully.',
            'filter'            => $statusFilter,
            'total'             => $formatted->count(),
            'counts'            => [
                'active'        => $activeCount,
                'transferred'   => $transferredCount,
                'total'         => Booking::where('user_id', $user->id)->count(),
            ],
            'data'              => $formatted,
        ], 200);
    }

    /**
     * POST /api/bookings or /api/customer/bookings
     * Create new confirmed Flexi-Booking entry OR list bookings if request specifies retrieval.
     */
    public function store(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true);
            if (!is_array($bodyJson)) {
                $bodyJson = [];
            }

            // Fallback: If JSON was passed as query string
            $rawQuery = urldecode($request->getQueryString() ?? '');
            if (empty($bodyJson) && str_starts_with(trim($rawQuery), '{') && str_ends_with(trim($rawQuery), '}')) {
                $bodyJson = json_decode($rawQuery, true) ?? [];
            }

            // Check if this POST is intended to retrieve/list bookings
            $hasStatus = $request->has('status') || !empty($bodyJson['status']);
            $action = strtolower($request->input('action') ?? ($bodyJson['action'] ?? ''));
            $isListAction = in_array($action, ['list', 'index', 'get']);
            $hasToken = $request->has('tokenAmount') || $request->has('token_amount') || $request->has('booking_amount')
                || !empty($bodyJson['tokenAmount']) || !empty($bodyJson['token_amount']) || !empty($bodyJson['booking_amount']);
            $hasTotalMrp = $request->has('totalMRP') || $request->has('total_mrp') || !empty($bodyJson['totalMRP']) || !empty($bodyJson['total_mrp']);
            $isExplicitCreate = in_array($action, ['create', 'store', 'book']);

            if (($hasStatus || $isListAction || (!$hasToken && !$hasTotalMrp)) && !$isExplicitCreate) {
                return $this->index($request);
            }

            // 1. Resolve Customer Details
            $customerDetails = $request->input('customerDetails') ?? ($bodyJson['customerDetails'] ?? []);
            if (is_string($customerDetails)) {
                $decoded = json_decode($customerDetails, true);
                $customerDetails = is_array($decoded) ? $decoded : [];
            }

            $user = $this->resolveCustomer($request, $customerDetails);

            $custName  = $customerDetails['name']
                ?? $request->input('customer_name')
                ?? ($bodyJson['customer_name'] ?? null);

            $custPhone = $customerDetails['phone']
                ?? $request->input('customer_phone')
                ?? $request->input('phone')
                ?? ($bodyJson['customer_phone'] ?? ($bodyJson['phone'] ?? null));

            $custEmail = $customerDetails['email']
                ?? $request->input('customer_email')
                ?? $request->input('email')
                ?? ($bodyJson['customer_email'] ?? ($bodyJson['email'] ?? null));

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required to create a booking.',
                ], 401);
            }

            // 2. Resolve Product
            $productId = $request->input('productId')
                ?? $request->input('product_id')
                ?? ($bodyJson['productId'] ?? null)
                ?? ($bodyJson['product_id'] ?? null);

            if (empty($productId)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'productId is required to create a booking.',
                ], 422);
            }

            $product = is_numeric($productId)
                ? Product::find($productId)
                : Product::where('slug', $productId)->orWhere('sku', $productId)->first();

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found. Please provide a valid productId.',
                ], 404);
            }

            // 3. Resolve Color & Quantity
            $selectedColor = $request->input('selectedColor')
                ?? $request->input('selected_color')
                ?? $request->input('color')
                ?? ($bodyJson['selectedColor'] ?? null)
                ?? ($bodyJson['selected_color'] ?? ($bodyJson['color'] ?? null));

            $quantity = (int) (
                $request->input('quantity')
                ?? ($bodyJson['quantity'] ?? 1)
            );
            if ($quantity < 1) {
                $quantity = 1;
            }

            // 4. Resolve Address (1. Request payload -> 2. Saved user_addresses -> 3. Customer profile table)
            $addressInput = $request->input('address') ?? ($bodyJson['address'] ?? null);
            if (is_string($addressInput) && str_starts_with(trim($addressInput), '{')) {
                $decodedAddr = json_decode($addressInput, true);
                if (is_array($decodedAddr)) {
                    $addressInput = $decodedAddr;
                }
            }
            $shippingAddress = null;
            $city    = null;
            $state   = null;
            $pincode = null;

            if (!empty($addressInput)) {
                if (is_array($addressInput)) {
                    $shippingAddress = $addressInput['address_line']
                        ?? ($addressInput['address'] ?? ($addressInput['street'] ?? ($addressInput['line1'] ?? '')));
                    $city    = $addressInput['city'] ?? ($request->input('city') ?? ($bodyJson['city'] ?? ''));
                    $state   = $addressInput['state'] ?? ($request->input('state') ?? ($bodyJson['state'] ?? ''));
                    $pincode = $addressInput['pincode'] ?? ($addressInput['zip'] ?? ($request->input('pincode') ?? ($bodyJson['pincode'] ?? '')));
                } else {
                    $shippingAddress = (string) $addressInput;
                    $city    = $request->input('city') ?? ($bodyJson['city'] ?? '');
                    $state   = $request->input('state') ?? ($bodyJson['state'] ?? '');
                    $pincode = $request->input('pincode') ?? ($bodyJson['pincode'] ?? '');
                }
            } elseif ($request->filled('shipping_address') || !empty($bodyJson['shipping_address'])) {
                $shippingAddress = $request->input('shipping_address') ?? $bodyJson['shipping_address'];
                $city    = $request->input('city') ?? ($bodyJson['city'] ?? '');
                $state   = $request->input('state') ?? ($bodyJson['state'] ?? '');
                $pincode = $request->input('pincode') ?? ($bodyJson['pincode'] ?? '');
            }

            // If no address was passed in request, automatically fetch from Customer's saved addresses or profile!
            if (empty($shippingAddress) && $user) {
                // Priority A: Default saved address from user_addresses table
                $savedAddress = \App\Models\UserAddress::where('user_id', $user->id)
                    ->orderBy('is_default', 'desc')
                    ->latest()
                    ->first();

                if ($savedAddress) {
                    $shippingAddress = $savedAddress->street;
                    $city    = $savedAddress->city;
                    $state   = $savedAddress->state;
                    $pincode = $savedAddress->pincode;
                }
                // Priority B: Address fields saved directly in users profile table
                elseif (!empty($user->address)) {
                    $shippingAddress = $user->address;
                    $city    = $user->city;
                    $state   = $user->state;
                    $pincode = $user->pincode;
                }
            }

            // If still empty (customer has no saved address and did not send one in the request)
            if (empty($shippingAddress)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Delivery address is required. Please provide address in the request or save one in your profile.',
                ], 422);
            }

            if (empty($city)) { $city = $user?->city ?: 'Pune'; }
            if (empty($state)) { $state = $user?->state ?: 'Maharashtra'; }
            if (empty($pincode)) { $pincode = $user?->pincode ?: '411001'; }

            // 5. Calculate Pricing (Total MRP, 20% Token Amount, 80% Balance Amount)
            $mrpInput = $request->input('totalMRP')
                ?? $request->input('total_mrp')
                ?? $request->input('mrp')
                ?? ($bodyJson['totalMRP'] ?? null)
                ?? ($bodyJson['total_mrp'] ?? null);

            $totalMRP = !empty($mrpInput) ? (float) $mrpInput : ((float) $product->mrp * $quantity);

            $paymentType = $request->input('payment_type')
                ?? $request->input('paymentType')
                ?? ($bodyJson['payment_type'] ?? null)
                ?? ($bodyJson['paymentType'] ?? 'booking_20');

            if ($paymentType === 'full_payment') {
                $bookingAmount = $totalMRP;
                $balanceAmount = 0.00;
                $paymentStatus = 'fully_paid';
                $bookingStatus = 'completed';
            } else {
                $tokenInput = $request->input('tokenAmount')
                    ?? $request->input('token_amount')
                    ?? $request->input('booking_amount')
                    ?? ($bodyJson['tokenAmount'] ?? null)
                    ?? ($bodyJson['token_amount'] ?? null);

                $bookingPct = (float) ($product->booking_percentage ?: 20.00);
                $bookingAmount = !empty($tokenInput) ? (float) $tokenInput : round($totalMRP * ($bookingPct / 100), 2);
                $balanceAmount = round($totalMRP - $bookingAmount, 2);
                $paymentStatus = 'paid'; // 20% confirmed token paid
                $bookingStatus = 'booked';
            }

            $bookingDate = now();
            $balanceDueDate = now()->addDays(60);
            $bookingNumber = 'NEX-' . date('Y') . '-' . rand(100000, 999999);
            $qrHash = md5($bookingNumber . $user->id . time());

            $nonRefundableAccepted = (bool) (
                $request->input('non_refundable_accepted')
                ?? ($bodyJson['non_refundable_accepted'] ?? true)
            );

            // DSP Partner matching or selection
            $matchingService = app(\App\Services\DspMatchingService::class);
            $dspId = $request->input('dsp_id') ?? ($bodyJson['dsp_id'] ?? null);
            if (empty($dspId)) {
                $matchedDsp = $matchingService->getBestMatchingDsp($pincode, $city, $state);
                $dspId = $matchedDsp?->id;
            }

            $booking = Booking::create([
                'booking_number'          => $bookingNumber,
                'user_id'                 => $user->id,
                'dsp_id'                  => $dspId,
                'product_id'              => $product->id,
                'product_name'            => $product->name,
                'model_code'              => $product->model_code,
                'selected_color'          => $selectedColor,
                'quantity'                => $quantity,
                'mrp'                     => $totalMRP,
                'booking_amount'          => $bookingAmount,
                'balance_amount'          => $balanceAmount,
                'booking_date'            => $bookingDate,
                'balance_due_date'        => $balanceDueDate,
                'payment_type'            => $paymentType,
                'payment_status'          => $paymentStatus,
                'booking_status'          => $bookingStatus,
                'transfer_status'         => 'original',
                'non_refundable_accepted' => $nonRefundableAccepted,
                'customer_name'           => $custName ?: ($user->name ?: 'NEXVIA Customer'),
                'customer_phone'          => $custPhone ?: ($user->phone ?: '9876543210'),
                'customer_email'          => $custEmail ?: ($user->email ?: null),
                'shipping_address'        => $shippingAddress,
                'pincode'                 => $pincode,
                'city'                    => $city,
                'state'                   => $state,
                'qr_code_hash'            => $qrHash,
                'payment_receipt'         => (function() use ($request, $bookingNumber) {
                    if ($request->hasFile('payment_receipt')) {
                        $f = $request->file('payment_receipt');
                        $n = 'booking_' . $bookingNumber . '_' . time() . '.' . $f->getClientOriginalExtension();
                        $dest = public_path('uploads/payment_receipts');
                        if (!file_exists($dest)) mkdir($dest, 0755, true);
                        $f->move($dest, $n);
                        return 'uploads/payment_receipts/' . $n;
                    }
                    return null;
                })(),
            ]);

            // Initialize Delivery Record for DSP Partner
            $trackingNumber = 'TRK-' . rand(10000000, 99999999);
            \App\Models\Delivery::create([
                'booking_id'      => $booking->id,
                'dsp_id'          => $dspId,
                'tracking_number' => $trackingNumber,
                'stage'           => 'order_confirmed',
            ]);

            // 6. Link referral code if passed during checkout
            $refCode = $request->input('referral_code') ?? ($bodyJson['referral_code'] ?? null);
            if (!empty($refCode)) {
                $referrer = Customer::where('referral_code', strtoupper(trim($refCode)))->first();
                if ($referrer && $referrer->id !== $user->id && empty($user->referred_by_id)) {
                    $user->referred_by_id = $referrer->id;
                    $user->save();
                }
            }

            // 7. Process referral points for referrer if customer was referred
            $referralService = app(ReferralCommissionService::class);
            $referral = $referralService->processReferralBooking($booking);

            // 8. Activate or Reactivate Self Dealer status for buyer if product is self-dealer eligible
            $activatedSelfDealer = false;
            if ($product->self_dealer_eligible && (!$user->is_self_dealer || $user->self_dealer_status !== 'active')) {
                $activatedSelfDealer = $referralService->activateSelfDealer($user, $booking);
            }

            // 9. Auto-approve pending referrals if full payment was completed upfront
            if ($paymentStatus === 'fully_paid') {
                $referralService->autoApprovePendingReferralsForBooking($booking);
            }

            // 10. Send booking confirmation email via SMTP
            $targetEmail = $booking->customer_email ?: ($user->email ?? null);
            if (!empty($targetEmail)) {
                try {
                    $appName = config('mail.from.name', 'NEXVIA');
                    $amtPaid = number_format($booking->booking_amount, 2);
                    $balAmt  = number_format($booking->balance_amount, 2);
                    $dueDate = \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y');
                    Mail::raw("Dear {$booking->customer_name},\n\nThank you for choosing {$appName}! Your booking has been successfully placed.\n\nBooking ID: {$booking->booking_number}\nProduct: {$booking->product_name}\nQuantity: {$booking->quantity}\nAmount Paid: ₹{$amtPaid}\nBalance Remaining: ₹{$balAmt}\nBalance Due Date: {$dueDate}\nDelivery Address: {$booking->shipping_address}, {$booking->city}, {$booking->state} - {$booking->pincode}\n\nWe will keep you updated on your order progress!\n\nBest regards,\n{$appName} Team", function ($m) use ($targetEmail, $booking, $appName) {
                        $m->to($targetEmail)
                          ->subject("{$appName} Booking Confirmation - {$booking->booking_number}");
                    });
                } catch (\Throwable $e) {
                    \Log::warning("Booking confirmation email failed: " . $e->getMessage());
                }
            }

            $mainImageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);

            return response()->json([
                'status'  => true,
                'message' => 'New confirmed Flexi-Booking entry created successfully.',
                'booking' => [
                    'id'                      => $booking->id,
                    'booking_number'          => $booking->booking_number,
                    'product'                 => [
                        'id'                  => $product->id,
                        'name'                => $product->name,
                        'slug'                => $product->slug,
                        'model_code'          => $product->model_code,
                        'sku'                 => $product->sku,
                        'selected_color'      => $booking->selected_color,
                        'quantity'            => (int) $booking->quantity,
                        'main_image'          => $mainImageUrl,
                        'imageUrl'            => $mainImageUrl,
                    ],
                    'financials'              => [
                        'total_mrp'           => (float) $booking->mrp,
                        'token_amount_paid'   => (float) $booking->booking_amount,
                        'balance_amount_due'  => (float) $booking->balance_amount,
                        'token_percentage'    => 20.0,
                        'balance_percentage'  => 80.0,
                        'currency'            => 'INR',
                    ],
                    'timeline'                => [
                        'booking_date'        => $booking->booking_date->format('Y-m-d'),
                        'balance_due_date'    => $booking->balance_due_date->format('Y-m-d'),
                        'days_remaining'      => $booking->days_remaining,
                        'due_in_days_text'    => "Payable within {$booking->days_remaining} days before delivery",
                    ],
                    'customer'                => [
                        'user_id'             => $user->id,
                        'name'                => $booking->customer_name,
                        'phone'               => $booking->customer_phone,
                        'email'               => $booking->customer_email,
                    ],
                    'shipping_address'        => [
                        'address'             => $booking->shipping_address,
                        'city'                => $booking->city,
                        'state'               => $booking->state,
                        'pincode'             => $booking->pincode,
                    ],
                    'status'                  => [
                        'booking_status'      => $booking->booking_status,
                        'payment_status'      => $booking->payment_status,
                        'transfer_status'     => $booking->transfer_status,
                        'transfer_eligible'   => true,
                        'non_refundable'      => $booking->non_refundable_accepted,
                    ],
                    'qr_code_hash'            => $booking->qr_code_hash,
                    'created_at'              => $booking->created_at->toIso8601String(),
                ],
                'data' => [
                    'id'               => $booking->id,
                    'booking_number'   => $booking->booking_number,
                    'product_name'     => $booking->product_name,
                    'mrp'              => (float)$booking->mrp,
                    'booking_amount'   => (float)$booking->booking_amount,
                    'balance_amount'   => (float)$booking->balance_amount,
                    'booking_date'     => $booking->booking_date->format('Y-m-d'),
                    'balance_due_date' => $booking->balance_due_date->format('Y-m-d'),
                    'days_remaining'   => $booking->days_remaining,
                    'qr_code_hash'     => $booking->qr_code_hash,
                ],
                'upi_qr' => [
                    'enabled'       => true,
                    'account_name'  => 'DLS AGRO INFRAVENTURE PRIVATE LIMITED',
                    'upi_id'        => 'dlsagroin.09@idfcbank',
                    'bank_name'     => 'IDFC FIRST Bank',
                    'qr_image_url'  => asset('images/dls_payment_qr.png'),
                    'instructions'  => 'Scan this QR code with any UPI app to transfer booking or balance payment',
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create booking.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/bookings/{id}
     * Get single booking details & receipt data.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->with('product')
            ->first();

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                      => $booking->id,
                'booking_number'          => $booking->booking_number,
                'customer_name'           => $booking->customer_name,
                'customer_phone'          => $booking->customer_phone,
                'product_name'            => $booking->product_name,
                'model_code'              => $booking->model_code,
                'mrp'                     => (float)$booking->mrp,
                'booking_amount'          => (float)$booking->booking_amount,
                'balance_amount'          => (float)$booking->balance_amount,
                'booking_date'            => $booking->booking_date->format('Y-m-d'),
                'balance_due_date'        => $booking->balance_due_date->format('Y-m-d'),
                'days_remaining'          => $booking->days_remaining,
                'is_overdue'              => $booking->is_overdue,
                'is_expired_60_days'      => ($booking->days_remaining <= 0) || \Carbon\Carbon::parse($booking->balance_due_date)->isPast(),
                'can_reallocate_paid_amount' => (($booking->days_remaining <= 0) || \Carbon\Carbon::parse($booking->balance_due_date)->isPast()) && ($booking->payment_status !== 'fully_paid') && ($booking->booking_status !== 'reallocated'),
                'filled_amount'           => max(0.00, round(((float)$booking->mrp) - ((float)$booking->balance_amount), 2)),
                'cancellation_allowed'    => false,
                'payment_type'            => $booking->payment_type,
                'payment_status'          => $booking->payment_status,
                'booking_status'          => $booking->booking_status,
                'transfer_status'         => $booking->transfer_status,
                'shipping_address'        => $booking->shipping_address,
                'city'                    => $booking->city,
                'state'                   => $booking->state,
                'pincode'                 => $booking->pincode,
                'qr_code_hash'            => $booking->qr_code_hash,
                'non_refundable_accepted' => $booking->non_refundable_accepted,
                'upi_qr'                  => [
                    'enabled'       => true,
                    'account_name'  => 'DLS AGRO INFRAVENTURE PRIVATE LIMITED',
                    'upi_id'        => 'dlsagroin.09@idfcbank',
                    'bank_name'     => 'IDFC FIRST Bank',
                    'qr_image_url'  => asset('images/dls_payment_qr.png'),
                    'instructions'  => 'Scan this QR code with any UPI app to transfer remaining balance',
                ],
            ],
        ]);
    }

    /**
     * POST /api/customer/bookings/{id}/pay-balance
     * Pay remaining 80% balance using cash OR NEXVIA Product Credit Wallet.
     */
    public function payBalance(Request $request, $id)
    {
        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $booking = Booking::where('id', $id)->where('user_id', $user->id)->first();
        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        if ($booking->payment_status === 'fully_paid') {
            return response()->json(['status' => false, 'message' => 'Booking is already fully paid.'], 422);
        }

        $mode = $request->input('payment_mode', 'full'); // full, emi, flexible
        $currentBalance = (float)$booking->balance_amount;
        $amountToPay = $currentBalance;
        $tenure = null;
        $emiMonthly = (float)($booking->emi_monthly_amount ?? 0);

        if ($mode === 'flexible') {
            $custom = (float)$request->input('custom_amount', 0);
            if ($custom > 0) {
                $amountToPay = min($custom, $currentBalance);
            }
        } elseif ($mode === 'emi') {
            $tenure = (int)($request->input('emi_tenure') ?: ($booking->emi_tenure_months ?: 6));
            if ($emiMonthly <= 0 || (int)$booking->emi_tenure_months !== $tenure) {
                $emiMonthly = round($currentBalance / $tenure, 2);
            }
            $amountToPay = min($emiMonthly, $currentBalance);
        }

        $useWalletCredit = (bool)$request->input('use_product_credit', false);
        $balanceDue = $amountToPay;
        $creditApplied = 0.00;

        if ($useWalletCredit) {
            $availableWallet = (float)($user->wallet_balance ?? 0);
            $creditApplied = min($availableWallet, $balanceDue);
        }

        $remainingCashToPay = max(0, $balanceDue - $creditApplied);

        // Apply product credit if used
        if ($creditApplied > 0) {
            $user->wallet_balance = max(0, ($user->wallet_balance ?? 0) - $creditApplied);
            $user->save();

            // Sync with SelfDealerWallet
            $dealerWallet = \App\Models\SelfDealerWallet::where('user_id', $user->id)->first();
            if ($dealerWallet) {
                $dealerWallet->redeemPoints($creditApplied);
            }

            WalletTransaction::create([
                'user_id'          => $user->id,
                'amount'           => $creditApplied,
                'type'             => 'debit',
                'source'           => 'booking_redemption',
                'transaction_type' => 'redemption',
                'status'           => 'redeemed',
                'booking_id'       => $booking->id,
                'description'      => "Redeemed NEXVIA Product Credit (₹" . number_format($creditApplied, 2) . ") toward balance for booking {$booking->booking_number}",
            ]);
        }

        $newBalance = max(0, round($currentBalance - ($creditApplied + $remainingCashToPay), 2));
        $isFullyPaid = ($newBalance <= 0);

        // Optional Receipt Upload for Balance Payment
        $balanceReceiptPath = null;
        if ($request->hasFile('payment_receipt')) {
            $rFile = $request->file('payment_receipt');
            $rName = 'bal_' . $booking->booking_number . '_' . time() . '.' . $rFile->getClientOriginalExtension();
            $destination = public_path('uploads/payment_receipts');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $rFile->move($destination, $rName);
            $balanceReceiptPath = 'uploads/payment_receipts/' . $rName;
        }

        // Record history entry
        $history = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
        $installmentNo = ($mode === 'emi') ? (($booking->emi_installments_paid ?? 0) + 1) : null;

        $history[] = [
            'payment_id'     => 'BAL-' . strtoupper(Str::random(8)),
            'mode'           => $mode,
            'amount'         => ($creditApplied + $remainingCashToPay),
            'credit_applied' => $creditApplied,
            'cash_paid'      => $remainingCashToPay,
            'tenure_months'  => $tenure,
            'installment_no' => $installmentNo,
            'reference_no'   => $request->input('reference_no') ?: ('API-' . rand(10000000, 99999999)),
            'receipt_file'   => $balanceReceiptPath,
            'paid_at'        => now()->toDateTimeString(),
        ];

        $updateData = [
            'balance_amount'           => $newBalance,
            'balance_payment_mode'     => $mode,
            'balance_payments_history' => $history,
            'payment_status'           => $isFullyPaid ? 'fully_paid' : 'partial_paid',
            'booking_status'           => $isFullyPaid ? 'balance_paid' : $booking->booking_status,
        ];

        if ($balanceReceiptPath) {
            $updateData['payment_receipt'] = $balanceReceiptPath;
        }

        if ($mode === 'emi') {
            $updateData['emi_tenure_months']    = $tenure;
            $updateData['emi_monthly_amount']   = $emiMonthly;
            $updateData['emi_installments_paid'] = ($booking->emi_installments_paid ?? 0) + 1;
        }

        $booking->update($updateData);

        // Auto-approve any pending referral/activation points linked to this now fully-paid booking
        $referralService = app(\App\Services\ReferralCommissionService::class);
        $autoApprovedCount = $referralService->autoApprovePendingReferralsForBooking($booking);

        // Enforce activation stake rule if wallet points were used
        $stakeResult = ['cancelled' => false];
        if ($creditApplied > 0) {
            $stakeResult = $referralService->checkAndEnforceActivationStake($user, $creditApplied);
        }

        $message = 'Balance payment completed successfully.';
        if ($autoApprovedCount > 0) {
            $message .= " {$autoApprovedCount} pending credit/referral reward(s) have been auto-approved to available balance.";
        }
        if ($stakeResult['cancelled']) {
            $message .= ' Notice: Your Self-Dealer status has been cancelled because your initial 20% activation credit was consumed. Purchase an eligible product to reactivate.';
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => [
                'booking_number'          => $booking->booking_number,
                'credit_applied'          => (float)$creditApplied,
                'cash_paid'               => (float)$remainingCashToPay,
                'balance_amount'          => (float)$booking->balance_amount,
                'payment_status'          => $booking->payment_status,
                'referrals_auto_approved' => $autoApprovedCount,
                'wallet_balance_remain'   => (float)($user->wallet_balance ?? 0),
                'self_dealer_cancelled'   => $stakeResult['cancelled'],
                'self_dealer_status'      => $user->fresh()->self_dealer_status,
                'is_self_dealer'          => (bool) $user->fresh()->is_self_dealer,
            ],
        ]);
    }

    /**
     * POST /api/customer/bookings/{id}/transfer
     * Initiate booking transfer to new recipient name & mobile.
     */
    public function initiateTransfer(Request $request, $id)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $booking = Booking::where('id', $id)->where('user_id', $user->id)->first();
        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'to_name'  => 'required|string|max:255',
            'to_phone' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $newCustomer = Customer::where('phone', $request->to_phone)->first();

        $transfer = BookingTransfer::create([
            'booking_id'              => $booking->id,
            'from_user_id'            => $user->id,
            'to_name'                 => trim($request->to_name),
            'to_phone'                => trim($request->to_phone),
            'to_user_id'              => $newCustomer ? $newCustomer->id : null,
            'transfer_otp'            => $otp,
            'transfer_otp_expires_at' => now()->addMinutes(15),
            'status'                  => 'pending',
        ]);

        return response()->json([
            'status'     => true,
            'message'    => 'Transfer OTP generated. Verify OTP to complete transfer.',
            'transfer_id'=> $transfer->id,
            'otp_debug'  => config('app.debug') ? $otp : null,
        ]);
    }

    /**
     * POST /api/customer/bookings/{id}/transfer/confirm
     * Confirm transfer with OTP and transfer booking ownership.
     */
    public function confirmTransfer(Request $request, $id)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'transfer_id' => 'required|exists:booking_transfers,id',
            'otp'         => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $transfer = BookingTransfer::where('id', $request->transfer_id)
            ->where('from_user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$transfer) {
            return response()->json(['status' => false, 'message' => 'Pending transfer request not found.'], 404);
        }

        if ($transfer->transfer_otp !== $request->otp || Carbon::now()->greaterThan($transfer->transfer_otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired transfer OTP.'], 422);
        }

        // Find or auto-create new recipient customer account
        $recipient = Customer::where('phone', $transfer->to_phone)->first();
        if (!$recipient) {
            $recipient = Customer::create([
                'name'     => $transfer->to_name,
                'phone'    => $transfer->to_phone,
                'password' => Str::random(10),
                'status'   => 'active',
            ]);
        }

        $booking = Booking::findOrFail($id);
        $booking->transfer_status = 'pending_approval';
        $booking->save();

        $transfer->to_user_id = $recipient->id;
        $transfer->status     = 'pending_admin_approval';
        $transfer->save();

        return response()->json([
            'status'  => true,
            'message' => 'OTP verified successfully. Your booking transfer request has been submitted and is pending Admin Approval.',
            'data'    => [
                'booking_number'   => $booking->booking_number,
                'recipient_name'   => $recipient->name,
                'recipient_phone'  => $recipient->phone,
                'transfer_status'  => 'pending_admin_approval',
            ],
        ]);
    }

    /**
     * POST /api/customer/bookings/{id}/cancel
     * Cancel an active booking, reverse any pending/available referral credits,
     * revoke Self-Dealer status if this was the activation booking, and refund redeemed credits if any.
     */
    public function cancel(Request $request, $id)
    {
        return response()->json([
            'status'  => false,
            'message' => 'Cancellation is not permitted. Once the 20% booking deposit is confirmed, bookings are strictly non-cancellable and non-refundable.',
        ], 422);
    }

    /**
     * POST /api/customer/bookings/{id}/reallocate
     * POST /api/bookings/{id}/reallocate
     * If 60 days have passed and the balance is unpaid, customer can reallocate
     * their total paid amount to their Product Credit wallet to buy another item.
     */
    public function reallocate(Request $request, $id)
    {
        $user = $this->resolveCustomer($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $booking = Booking::where('id', $id)->where('user_id', $user->id)->first();
        if (!$booking) {
            $booking = Booking::where('booking_number', $id)->where('user_id', $user->id)->first();
        }

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        if ($booking->payment_status === 'fully_paid') {
            return response()->json([
                'status'  => false,
                'message' => 'This booking is already fully paid and cannot be reallocated.',
            ], 422);
        }

        if ($booking->booking_status === 'reallocated') {
            return response()->json([
                'status'  => false,
                'message' => 'The paid amount for this booking has already been reallocated to product credit.',
            ], 422);
        }

        $isExpired = ($booking->days_remaining <= 0) || Carbon::parse($booking->balance_due_date)->isPast();
        if (!$isExpired) {
            return response()->json([
                'status'  => false,
                'message' => "Reallocation is available only after the 60-day settlement period expires ({$booking->days_remaining} days remaining).",
            ], 422);
        }

        $totalPaid = max(0.00, round(((float)$booking->mrp) - ((float)$booking->balance_amount), 2));
        if ($totalPaid <= 0) {
            $totalPaid = (float) $booking->booking_amount;
        }

        return DB::transaction(function () use ($booking, $user, $totalPaid) {
            $user->wallet_balance = ($user->wallet_balance ?? 0) + $totalPaid;
            $user->save();

            $dealerWallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);
            $dealerWallet->creditAvailable($totalPaid);

            WalletTransaction::create([
                'user_id'          => $user->id,
                'amount'           => $totalPaid,
                'type'             => 'credit',
                'source'           => 'booking_reallocation',
                'booking_id'       => $booking->id,
                'description'      => "Reallocated paid amount ₹" . number_format($totalPaid, 2) . " from 60-day expired booking #{$booking->booking_number} to buy another item",
                'transaction_type' => 'reallocation',
                'status'           => 'available',
                'available_at'     => now(),
            ]);

            $booking->booking_status = 'reallocated';
            $booking->save();

            return response()->json([
                'status'  => true,
                'message' => "Your paid amount of ₹" . number_format($totalPaid, 2) . " has been transferred to your Product Credit wallet. You can now use it to purchase another catalog item.",
                'data'    => [
                    'booking_number'        => $booking->booking_number,
                    'booking_status'        => $booking->booking_status,
                    'reallocated_amount'    => $totalPaid,
                    'wallet_balance'        => (float) $user->fresh()->wallet_balance,
                ],
            ], 200);
        });
    }

    /**
     * POST /api/customer/bookings/{id}/select-dsp or /api/bookings/{id}/select-dsp
     * Select or assign an Authorised DSP Partner to deliver the booking to customer.
     */
    public function selectDsp(Request $request, $id)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $user = $this->resolveCustomer($request);

        $booking = is_numeric($id) ? Booking::find($id) : Booking::where('booking_number', $id)->first();
        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        if ($user && $booking->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to this booking.'], 403);
        }

        $dspId = $request->input('dsp_id') ?? ($bodyJson['dsp_id'] ?? null);
        if (empty($dspId)) {
            return response()->json([
                'status'  => false,
                'message' => 'dsp_id is required to select a delivery partner.',
            ], 422);
        }

        $dsp = DspApplication::find($dspId);
        if (!$dsp) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected DSP partner does not exist.',
            ], 404);
        }

        $booking->dsp_id = $dsp->id;
        $booking->save();

        // If delivery record exists, sync the DSP assignment
        if ($booking->delivery) {
            $booking->delivery->update(['dsp_id' => $dsp->id]);
        }

        return response()->json([
            'status'  => true,
            'message' => "DSP Partner '{$dsp->business_name}' assigned to booking #{$booking->booking_number}. Delivery and unboxing will be coordinated by this partner.",
            'booking_number' => $booking->booking_number,
            'assigned_dsp'   => [
                'id'            => $dsp->id,
                'business_name' => $dsp->business_name ?: $dsp->applicant_name,
                'mobile'        => $dsp->mobile,
                'territory'     => $dsp->preferred_territory_area,
                'address'       => $dsp->complete_address,
                'pincode'       => $dsp->premises_pincode,
                'district'      => $dsp->district,
                'state'         => $dsp->state,
            ],
        ], 200);
    }
}

