<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Product;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Services\ReferralCommissionService;
use Illuminate\Http\Request;
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
            $bodyJson = json_decode($request->getContent(), true);
            if (!is_array($bodyJson)) {
                $bodyJson = [];
            }

            $userId = $request->input('user_id')
                ?? $request->input('userId')
                ?? ($customerDetails['userId'] ?? null)
                ?? ($customerDetails['user_id'] ?? null)
                ?? ($bodyJson['user_id'] ?? null)
                ?? ($bodyJson['userId'] ?? null);

            if (!empty($userId)) {
                $customer = Customer::find($userId);
            }
        }

        if (!$customer) {
            $phone = $customerDetails['phone']
                ?? $request->input('customer_phone')
                ?? $request->input('phone')
                ?? ($bodyJson['customer_phone'] ?? null)
                ?? ($bodyJson['phone'] ?? null);

            if (!empty($phone)) {
                $customer = Customer::where('phone', $phone)->first();
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
                if (!empty($custPhone)) {
                    $user = Customer::create([
                        'name'     => $custName ?: 'NEXVIA Customer',
                        'phone'    => $custPhone,
                        'email'    => $custEmail ?: 'customer_' . time() . rand(10, 99) . '@nexvia.in',
                        'password' => bcrypt(Str::random(12)),
                        'role'     => 'customer',
                    ]);
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Customer information is required. Provide user_id, or customerDetails with name and phone.',
                    ], 401);
                }
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

            $booking = Booking::create([
                'booking_number'          => $bookingNumber,
                'user_id'                 => $user->id,
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

            // 8. Activate Self Dealer status for buyer if product is self-dealer eligible
            $activatedSelfDealer = false;
            if ($product->self_dealer_eligible && !$user->is_self_dealer) {
                $activatedSelfDealer = $referralService->activateSelfDealer($user, $booking);
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
            ],
        ]);
    }

    /**
     * POST /api/customer/bookings/{id}/pay-balance
     * Pay remaining 80% balance using cash OR NEXVIA Product Credit Wallet.
     */
    public function payBalance(Request $request, $id)
    {
        $user = $request->user('customer');
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

        $useWalletCredit = (bool)$request->input('use_product_credit', false);
        $balanceDue = $booking->balance_amount;
        $creditApplied = 0.00;

        if ($useWalletCredit) {
            $availableWallet = (float)($user->wallet_balance ?? 0);
            $creditApplied = min($availableWallet, $balanceDue);
        }

        $remainingCashToPay = $balanceDue - $creditApplied;

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

        $booking->balance_amount = max(0, $booking->balance_amount - ($creditApplied + $remainingCashToPay));
        $booking->payment_status = 'fully_paid';
        $booking->booking_status = 'balance_paid';
        $booking->save();

        return response()->json([
            'status'  => true,
            'message' => 'Balance payment completed successfully.',
            'data'    => [
                'booking_number'        => $booking->booking_number,
                'credit_applied'        => (float)$creditApplied,
                'cash_paid'             => (float)$remainingCashToPay,
                'balance_amount'        => (float)$booking->balance_amount,
                'payment_status'        => $booking->payment_status,
                'wallet_balance_remain' => (float)($user->wallet_balance ?? 0),
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
}
