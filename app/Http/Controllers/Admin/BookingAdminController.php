<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DspApplication;
use App\Models\UserAddress;
use App\Models\Delivery;
use App\Services\DspMatchingService;
use App\Services\ReferralCommissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingAdminController extends Controller
{
    /**
     * List all customer bookings with channel filtering (online / offline) and search
     */
    public function index(Request $request)
    {
        $query = Booking::with(['product', 'user', 'delivery.dsp']);

        // Channel filter: online, offline, or all
        if ($request->filled('channel')) {
            if ($request->channel === 'offline') {
                $query->where('is_offline', true);
            } elseif ($request->channel === 'online') {
                $query->where('is_offline', false);
            }
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Booking status filter
        if ($request->filled('booking_status')) {
            $query->where('booking_status', $request->booking_status);
        }

        // Search by receipt, customer name, phone, or product
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('offline_payment_ref', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'       => Booking::count(),
            'online'      => Booking::where('is_offline', false)->count(),
            'offline'     => Booking::where('is_offline', true)->count(),
            'fully_paid'  => Booking::where('payment_status', 'fully_paid')->count(),
            'balance_due' => Booking::where('payment_status', '!=', 'fully_paid')->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Show form to manually record an offline customer purchase / booking
     */
    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'email', 'address', 'pincode', 'city', 'state']);
        $dsps = DspApplication::where('status', 'approved')->orderBy('business_name')->get();

        return view('admin.bookings.create', compact('products', 'customers', 'dsps'));
    }

    /**
     * Store new offline purchase / booking entry from Admin Panel
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_type'          => 'required|in:existing,new',
            'customer_id'            => 'nullable|required_if:customer_type,existing|exists:users,id',
            'customer_name'          => 'required|string|max:255',
            'customer_phone'         => 'required|string|max:20',
            'customer_email'         => 'nullable|email|max:255',
            'shipping_address'       => 'required|string',
            'pincode'                => 'required|string|max:10',
            'city'                   => 'required|string|max:100',
            'state'                  => 'required|string|max:100',
            'referrer_code'          => 'nullable|string|max:50',

            'product_id'             => 'required|exists:products,id',
            'selected_color'         => 'nullable|string|max:50',
            'quantity'               => 'required|integer|min:1|max:100',
            'mrp'                    => 'required|numeric|min:0',
            'payment_type'           => 'required|in:booking_20,full_payment,custom',
            'booking_amount'         => 'required|numeric|min:0',
            'balance_amount'         => 'nullable|numeric|min:0',
            'booking_date'           => 'required|date',
            'balance_due_date'       => 'nullable|date',

            'purchase_channel'       => 'required|string|max:50',
            'offline_payment_method' => 'required|string|max:50',
            'offline_payment_ref'    => 'nullable|string|max:100',
            'payment_status'         => 'required|in:paid,fully_paid',
            'booking_status'         => 'required|in:booked,balance_paid,completed',
            'dsp_id'                 => 'nullable|exists:dsp_applications,id',
            'payment_receipt'        => 'nullable|file|mimes:jpeg,jpg,png,pdf,webp|max:5120',
            'offline_notes'          => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Resolve or Create Customer User Account
            if ($request->customer_type === 'existing') {
                $user = Customer::findOrFail($request->customer_id);
            } else {
                $cleanPhone = trim($request->customer_phone);
                $user = Customer::where('phone', $cleanPhone)->first();
                if (!$user) {
                    $user = Customer::create([
                        'name'              => trim($request->customer_name),
                        'phone'             => $cleanPhone,
                        'email'             => $request->customer_email ?: ('user_' . preg_replace('/[^0-9]/', '', $cleanPhone) . '@nexvia.in'),
                        'address'           => $request->shipping_address,
                        'pincode'           => $request->pincode,
                        'city'              => $request->city,
                        'state'             => $request->state,
                        'status'            => 'active',
                        'password'          => bcrypt(Str::random(16)),
                        'phone_verified_at' => now(),
                    ]);
                }
            }

            // Optional referral link if provided and user doesn't already have a referrer
            if ($request->filled('referrer_code') && empty($user->referred_by_id)) {
                $refCode = trim($request->referrer_code);
                $referrer = Customer::where('referral_code', $refCode)
                    ->orWhere('self_dealer_code', $refCode)
                    ->first();

                if ($referrer && $referrer->id !== $user->id) {
                    $user->referred_by_id = $referrer->id;
                    $user->save();
                }
            }

            // Sync user address record
            UserAddress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'street'  => $request->shipping_address,
                    'pincode' => $request->pincode,
                ],
                [
                    'name'       => $user->name,
                    'phone'      => $user->phone,
                    'city'       => $request->city,
                    'state'      => $request->state,
                    'is_default' => true,
                ]
            );

            // 2. Product and Pricing
            $product       = Product::findOrFail($request->product_id);
            $qty           = (int) $request->quantity;
            $unitMrp       = (float) $request->mrp;
            $totalMrp      = round($unitMrp * $qty, 2);
            $bookingAmount = round((float) $request->booking_amount, 2);
            $balanceAmount = max(0.00, round($totalMrp - $bookingAmount, 2));

            // Force fully_paid if balance is 0
            $paymentStatus = ($balanceAmount <= 0.00 || $request->payment_type === 'full_payment') ? 'fully_paid' : $request->payment_status;
            if ($paymentStatus === 'fully_paid') {
                $balanceAmount = 0.00;
            }

            // Generate unique Booking Receipt Number
            do {
                $bookingNumber = 'NEX-OFF-' . date('Y') . '-' . strtoupper(Str::random(6));
            } while (Booking::where('booking_number', $bookingNumber)->exists());

            // 3. Handle optional Payment Proof / Receipt File Upload
            $receiptPath = null;
            if ($request->hasFile('payment_receipt')) {
                $rFile = $request->file('payment_receipt');
                $rName = 'offline_' . $bookingNumber . '_' . time() . '.' . $rFile->getClientOriginalExtension();
                $destination = public_path('uploads/payment_receipts');
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }
                $rFile->move($destination, $rName);
                $receiptPath = 'uploads/payment_receipts/' . $rName;
            }

            // 4. Match / Assign DSP
            $dspId = $request->dsp_id;
            if (empty($dspId)) {
                $matchingService = app(DspMatchingService::class);
                $matchedDsp = $matchingService->getBestMatchingDsp($request->pincode, $request->city, $request->state);
                $dspId = $matchedDsp?->id;
            }

            // 5. Initial Ledger Transaction
            $initialHistory = [];
            if ($bookingAmount > 0) {
                $initialHistory[] = [
                    'mode'           => ($paymentStatus === 'fully_paid') ? 'full' : 'advance',
                    'payment_method' => $request->offline_payment_method,
                    'amount'         => $bookingAmount,
                    'reference_no'   => $request->offline_payment_ref ?: ('OFFLINE-' . date('YmdHis')),
                    'receipt_file'   => $receiptPath,
                    'notes'          => 'Initial payment recorded via Admin Offline Entry (' . strtoupper(str_replace('_', ' ', $request->offline_payment_method)) . ')',
                    'paid_at'        => Carbon::parse($request->booking_date)->format('Y-m-d H:i:s'),
                ];
            }

            $bookingDate = Carbon::parse($request->booking_date);
            $balanceDueDate = $request->filled('balance_due_date')
                ? Carbon::parse($request->balance_due_date)
                : (clone $bookingDate)->addDays(60);

            // 6. Create the Booking Record
            $booking = Booking::create([
                'booking_number'           => $bookingNumber,
                'is_offline'               => true,
                'purchase_channel'         => $request->purchase_channel,
                'offline_payment_method'   => $request->offline_payment_method,
                'offline_payment_ref'      => $request->offline_payment_ref,
                'offline_notes'            => $request->offline_notes,
                'user_id'                  => $user->id,
                'dsp_id'                   => $dspId,
                'product_id'               => $product->id,
                'product_name'             => $product->name,
                'model_code'               => $product->model_code,
                'selected_color'           => $request->selected_color,
                'quantity'                 => $qty,
                'mrp'                      => $totalMrp,
                'booking_amount'           => $bookingAmount,
                'balance_amount'           => $balanceAmount,
                'booking_date'             => $bookingDate->toDateString(),
                'balance_due_date'         => $balanceDueDate->toDateString(),
                'payment_type'             => $request->payment_type,
                'payment_status'           => $paymentStatus,
                'booking_status'           => $request->booking_status,
                'transfer_status'          => 'original',
                'non_refundable_accepted'  => true,
                'customer_name'            => $user->name,
                'customer_phone'           => $user->phone,
                'customer_email'           => $user->email,
                'shipping_address'         => $request->shipping_address,
                'pincode'                  => $request->pincode,
                'city'                     => $request->city,
                'state'                    => $request->state,
                'qr_code_hash'             => md5($bookingNumber . Str::random(10)),
                'payment_receipt'          => $receiptPath,
                'balance_payments_history' => $initialHistory,
            ]);

            // 7. Initialize Delivery Tracking Record
            $trackingNumber = 'TRK-' . rand(10000000, 99999999);
            $initialStage = ($booking->booking_status === 'completed') ? 'delivered' : 'order_confirmed';
            Delivery::create([
                'booking_id'      => $booking->id,
                'dsp_id'          => $dspId,
                'tracking_number' => $trackingNumber,
                'stage'           => $initialStage,
                'delivered_at'    => ($initialStage === 'delivered') ? now() : null,
            ]);

            // 8. Process Referral Commission & Self Dealer Programs
            $referralService = app(ReferralCommissionService::class);
            if ($user && $user->referred_by_id) {
                $referralService->processReferralBooking($booking);
            }

            // Activate Self Dealer program if product is eligible and buyer isn't active self dealer yet
            if ($product->self_dealer_eligible && (!$user->is_self_dealer || $user->self_dealer_status !== 'active')) {
                $referralService->activateSelfDealer($user, $booking);
            }

            // Auto-approve pending referrals if full payment is received
            if ($booking->payment_status === 'fully_paid' || in_array($booking->booking_status, ['delivered', 'completed'])) {
                $referralService->autoApprovePendingReferralsForBooking($booking);
            }

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', "Offline purchase entry #{$booking->booking_number} created successfully! Delivery tracking and receipt generated.");
        });
    }

    /**
     * Show booking receipt details & audit
     */
    public function show($id)
    {
        $booking = Booking::with([
            'product',
            'user',
            'delivery.dsp',
            'dsp',
            'transfers.fromUser',
            'transfers.toUser'
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Record an offline balance payment for any booking
     */
    public function recordBalancePayment(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->payment_status === 'fully_paid' || (float) $booking->balance_amount <= 0) {
            return back()->with('info', "Booking #{$booking->booking_number} is already fully paid.");
        }

        $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . ((float) $booking->balance_amount + 0.01),
            'payment_method' => 'required|string|max:50',
            'reference_no'   => 'nullable|string|max:100',
            'paid_at'        => 'required|date',
            'receipt_file'   => 'nullable|file|mimes:jpeg,jpg,png,pdf,webp|max:5120',
            'notes'          => 'nullable|string|max:500',
        ]);

        $paymentAmount = round((float) $request->amount, 2);
        $currentBalance = (float) $booking->balance_amount;
        $newBalance = max(0.00, round($currentBalance - $paymentAmount, 2));

        // Upload receipt file if provided
        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $rFile = $request->file('receipt_file');
            $rName = 'bal_offline_' . $booking->booking_number . '_' . time() . '.' . $rFile->getClientOriginalExtension();
            $destination = public_path('uploads/payment_receipts');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $rFile->move($destination, $rName);
            $receiptPath = 'uploads/payment_receipts/' . $rName;
        }

        $history = is_array($booking->balance_payments_history) ? $booking->balance_payments_history : [];
        $history[] = [
            'mode'           => 'offline_balance',
            'payment_method' => $request->payment_method,
            'amount'         => $paymentAmount,
            'reference_no'   => $request->reference_no ?: ('BAL-' . date('YmdHis')),
            'receipt_file'   => $receiptPath,
            'notes'          => $request->notes ?: 'Offline balance payment received at counter',
            'paid_at'        => Carbon::parse($request->paid_at)->format('Y-m-d H:i:s'),
        ];

        $booking->balance_amount = $newBalance;
        $booking->balance_payments_history = $history;

        $autoApprovedMsg = '';
        if ($newBalance <= 0.00) {
            $booking->payment_status = 'fully_paid';
            if ($booking->booking_status === 'booked') {
                $booking->booking_status = 'balance_paid';
            }

            // Auto-approve pending referral credits
            $referralService = app(ReferralCommissionService::class);
            $approvedCount = $referralService->autoApprovePendingReferralsForBooking($booking);
            if ($approvedCount > 0) {
                $autoApprovedMsg = " ({$approvedCount} referral commission(s) auto-approved!)";
            }
        }

        $booking->save();

        return back()->with('success', "Balance payment of ₹" . number_format($paymentAmount, 2) . " successfully recorded!" . $autoApprovedMsg);
    }

    /**
     * Transfer audit records
     */
    public function transfers()
    {
        $transfers = BookingTransfer::with(['booking.product', 'fromUser', 'toUser'])->latest()->paginate(20);
        return view('admin.bookings.transfers', compact('transfers'));
    }

    public function approveTransfer($id)
    {
        $transfer = BookingTransfer::with('booking')->findOrFail($id);
        if ($transfer->status === 'completed') {
            return back()->with('error', 'Transfer has already been completed.');
        }

        $booking = $transfer->booking;
        if ($booking) {
            $booking->user_id         = $transfer->to_user_id ?: $booking->user_id;
            $booking->customer_name   = $transfer->to_name;
            $booking->customer_phone  = $transfer->to_phone;
            $booking->transfer_status = 'transferred';
            $booking->save();
        }

        $transfer->status         = 'completed';
        $transfer->transferred_at = now();
        $transfer->save();

        return back()->with('success', 'Booking transfer approved! Ownership successfully updated to new customer.');
    }

    public function rejectTransfer($id)
    {
        $transfer = BookingTransfer::with('booking')->findOrFail($id);
        $transfer->status = 'rejected';
        $transfer->save();

        if ($transfer->booking) {
            $transfer->booking->transfer_status = 'original';
            $transfer->booking->save();
        }

        return back()->with('success', 'Booking transfer request rejected.');
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $request->validate([
            'booking_status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $oldStatus = $booking->booking_status;

        $booking->update([
            'booking_status' => $request->booking_status,
            'payment_status' => $request->payment_status,
        ]);

        $referralService = app(ReferralCommissionService::class);

        // Auto-approve pending referrals if booking is fully paid or completed/delivered
        $approvedMsg = '';
        if ($booking->payment_status === 'fully_paid' || in_array($booking->booking_status, ['delivered', 'completed'])) {
            $approvedCount = $referralService->autoApprovePendingReferralsForBooking($booking);
            if ($approvedCount > 0) {
                $approvedMsg = " ({$approvedCount} referral credit(s) auto-approved)";
            }
        }

        // If booking was cancelled by Admin, reverse any active or pending referrals
        if ($booking->booking_status === 'cancelled' && $oldStatus !== 'cancelled') {
            $booking->cancelled_at = now();
            $booking->cancellation_reason = 'Cancelled by Admin';
            $booking->save();

            $referrals = \App\Models\Referral::where('booking_id', $booking->id)
                ->whereIn('status', ['pending', 'available'])
                ->get();

            foreach ($referrals as $ref) {
                $referralService->reverseReferral($ref, "Booking {$booking->booking_number} cancelled by Admin");
            }

            $user = $booking->user;
            if ($user && $user->activation_booking_id == $booking->id) {
                $user->is_self_dealer = false;
                $user->self_dealer_status = 'cancelled';
                $user->save();
            }
        }

        return back()->with('success', 'Booking status updated successfully!' . $approvedMsg);
    }
}
