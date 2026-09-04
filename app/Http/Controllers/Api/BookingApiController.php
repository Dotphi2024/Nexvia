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
    public function index(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $bookings = Booking::where('user_id', $user->id)
            ->with('product')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id'                      => $booking->id,
                    'booking_number'          => $booking->booking_number,
                    'product_id'              => $booking->product_id,
                    'product_name'            => $booking->product_name,
                    'model_code'              => $booking->model_code,
                    'mrp'                     => (float)$booking->mrp,
                    'booking_amount'          => (float)$booking->booking_amount,
                    'balance_amount'          => (float)$booking->balance_amount,
                    'booking_date'            => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                    'balance_due_date'        => $booking->balance_due_date ? $booking->balance_due_date->format('Y-m-d') : null,
                    'days_remaining'          => $booking->days_remaining,
                    'is_overdue'              => $booking->is_overdue,
                    'payment_type'            => $booking->payment_type,
                    'payment_status'          => $booking->payment_status,
                    'booking_status'          => $booking->booking_status,
                    'transfer_status'         => $booking->transfer_status,
                    'non_refundable_accepted' => $booking->non_refundable_accepted,
                    'customer_name'           => $booking->customer_name,
                    'customer_phone'          => $booking->customer_phone,
                    'shipping_address'        => $booking->shipping_address,
                    'qr_code_hash'            => $booking->qr_code_hash,
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $bookings,
        ]);
    }

    /**
     * POST /api/customer/bookings
     * Create 20% Booking or Full Payment.
     */
    public function store(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_id'              => 'required|exists:products,id',
            'shipping_address'        => 'required|string',
            'city'                    => 'required|string',
            'state'                   => 'required|string',
            'pincode'                 => 'required|string',
            'payment_type'            => 'nullable|in:booking_20,full_payment',
            'non_refundable_accepted' => 'required|boolean',
        ], [
            'non_refundable_accepted.required' => 'You must accept the non-refundable booking terms.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            $paymentType = $request->input('payment_type', 'booking_20');

            $mrp = $product->mrp;

            if ($paymentType === 'full_payment') {
                $bookingAmount = $mrp;
                $balanceAmount = 0.00;
                $paymentStatus = 'fully_paid';
                $bookingStatus = 'completed';
            } else {
                $bookingPct = $product->booking_percentage ?: 20;
                $bookingAmount = $mrp * ($bookingPct / 100);
                $balanceAmount = $mrp - $bookingAmount;
                $paymentStatus = 'paid'; // initial 20% paid
                $bookingStatus = 'booked';
            }

            $bookingDate = now();
            $balanceDueDate = now()->addDays(60);
            $bookingNumber = 'NEX-' . date('Y') . '-' . rand(100000, 999999);
            $qrHash = md5($bookingNumber . $user->id . time());

            $booking = Booking::create([
                'booking_number'          => $bookingNumber,
                'user_id'                 => $user->id,
                'product_id'              => $product->id,
                'product_name'            => $product->name,
                'model_code'              => $product->model_code,
                'mrp'                     => $mrp,
                'booking_amount'          => $bookingAmount,
                'balance_amount'          => $balanceAmount,
                'booking_date'            => $bookingDate,
                'balance_due_date'        => $balanceDueDate,
                'payment_type'            => $paymentType,
                'payment_status'          => $paymentStatus,
                'booking_status'          => $bookingStatus,
                'transfer_status'         => 'original',
                'non_refundable_accepted' => (bool)$request->non_refundable_accepted,
                'customer_name'           => $user->name,
                'customer_phone'          => $user->phone,
                'shipping_address'        => $request->shipping_address,
                'pincode'                 => $request->pincode,
                'city'                    => $request->city,
                'state'                   => $request->state,
                'qr_code_hash'            => $qrHash,
            ]);

            // Process referral commission reward if customer was referred
            app(ReferralCommissionService::class)->processBookingReferral($booking);

            return response()->json([
                'status'  => true,
                'message' => 'Booking created successfully.',
                'data'    => [
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
            $user->wallet_balance = ($user->wallet_balance ?? 0) - $creditApplied;
            $user->save();

            WalletTransaction::create([
                'user_id'     => $user->id,
                'amount'      => $creditApplied,
                'type'        => 'debit',
                'source'      => 'booking_redemption',
                'booking_id'  => $booking->id,
                'description' => "Redeemed NEXVIA Product Credit (₹" . number_format($creditApplied, 2) . ") toward balance for booking {$booking->booking_number}",
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
