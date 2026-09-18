<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Booking;
use App\Models\WalletTransaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function checkout(Request $request, $slug)
    {
        $product = Product::with('category')->where('slug', $slug)->firstOrFail();
        
        // Support 'type' or 'payment_type' query params (normalizing 'full', 'full_payment' -> 'full_payment')
        $rawType = $request->query('type') ?? $request->query('payment_type', 'booking_20');
        $paymentType = in_array(strtolower((string)$rawType), ['full', 'full_payment', '100', '100%']) ? 'full_payment' : 'booking_20';

        $user = Auth::guard('web')->user();

        $matchingService = app(\App\Services\DspMatchingService::class);
        $userPincode = $user?->pincode;
        $availableDsps = $matchingService->findAvailableDsps($userPincode);

        return view('frontend.booking.checkout', compact('product', 'paymentType', 'user', 'availableDsps'));
    }

    public function processCheckout(Request $request, $slug)
    {
        // Normalize payment_type before validation
        $rawPaymentType = $request->input('payment_type') ?? $request->input('type', 'booking_20');
        $paymentType = in_array(strtolower((string)$rawPaymentType), ['full', 'full_payment', '100', '100%']) ? 'full_payment' : 'booking_20';
        $request->merge(['payment_type' => $paymentType]);

        $rules = [
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'pincode'          => 'required|string|max:10',
            'city'             => 'required|string|max:100',
            'state'            => 'required|string|max:100',
            'payment_type'     => 'required|in:booking_20,full_payment',
        ];

        if ($paymentType === 'booking_20') {
            $rules['non_refundable_terms'] = 'required|accepted';
        }

        $request->validate($rules);

        $product = Product::with('category')->where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();
        if (!$user) {
            $user = Customer::where('phone', $request->customer_phone)->first();
            if (!$user) {
                $user = Customer::create([
                    'name'     => $request->customer_name,
                    'email'    => $request->customer_email ?? ('user_' . preg_replace('/[^0-9]/', '', $request->customer_phone) . '@nexvia.in'),
                    'phone'    => $request->customer_phone,
                    'address'  => $request->shipping_address,
                    'city'     => $request->city,
                    'state'    => $request->state,
                    'pincode'  => $request->pincode,
                    'status'   => 'active',
                    'password' => bcrypt(Str::random(16)),
                ]);
            }
        }

        $bookingNumber = 'NEX-' . date('Y') . '-' . strtoupper(Str::random(6));
        $bookingDate = Carbon::today();
        $balanceDueDate = Carbon::today()->addDays(60);

        if ($paymentType === 'full_payment') {
            $rawPayable = (float) $product->mrp;
            $bookingAmount = (float) $product->mrp;
            $balanceAmount = 0.00;
            $paymentStatus = 'fully_paid';
            $bookingStatus = 'completed';
        } else {
            $rawPayable = (float) $product->booking_amount;
            $bookingAmount = (float) $product->booking_amount;
            $balanceAmount = (float) $product->balance_amount;
            $paymentStatus = 'paid';
            $bookingStatus = 'booked';
        }

        // Apply Product Credit Wallet redemption if requested
        $appliedWalletDiscount = 0.00;
        if ($request->has('use_wallet') && $user && (float) $user->wallet_balance > 0) {
            $appliedWalletDiscount = min((float) $user->wallet_balance, $bookingAmount);
            $bookingAmount = max(0.00, $bookingAmount - $appliedWalletDiscount);

            // Deduct wallet balance
            $user->decrement('wallet_balance', $appliedWalletDiscount);

            // Record Debit Transaction
            WalletTransaction::create([
                'user_id'     => $user->id,
                'amount'      => $appliedWalletDiscount,
                'type'        => 'debit',
                'source'      => 'booking_redemption',
                'description' => "Redeemed Product Credit for Receipt #{$bookingNumber}",
            ]);
        }

        // DSP Partner Assignment by customer choice or territory pincode match
        $matchingService = app(\App\Services\DspMatchingService::class);
        $dspId = $request->input('dsp_id');
        if (empty($dspId)) {
            $matchedDsp = $matchingService->getBestMatchingDsp($request->pincode, $request->city, $request->state);
            $dspId = $matchedDsp?->id;
        }

        // Optional Payment Receipt Upload
        $receiptPath = null;
        if ($request->hasFile('payment_receipt')) {
            $rFile = $request->file('payment_receipt');
            $rName = 'booking_' . $bookingNumber . '_' . time() . '.' . $rFile->getClientOriginalExtension();
            $destination = public_path('uploads/payment_receipts');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $rFile->move($destination, $rName);
            $receiptPath = 'uploads/payment_receipts/' . $rName;
        }

        $booking = Booking::create([
            'booking_number'          => $bookingNumber,
            'user_id'                 => $user?->id,
            'dsp_id'                  => $dspId,
            'product_id'              => $product->id,
            'product_name'            => $product->name,
            'model_code'              => $product->model_code,
            'mrp'                     => $product->mrp,
            'booking_amount'          => $bookingAmount,
            'balance_amount'          => $balanceAmount,
            'booking_date'            => $bookingDate,
            'balance_due_date'        => $balanceDueDate,
            'payment_type'            => $paymentType,
            'payment_status'          => $paymentStatus,
            'booking_status'          => $bookingStatus,
            'transfer_status'         => 'original',
            'non_refundable_accepted' => true,
            'customer_name'           => $request->customer_name,
            'customer_phone'          => $request->customer_phone,
            'shipping_address'        => $request->shipping_address,
            'pincode'                 => $request->pincode,
            'city'                    => $request->city,
            'state'                   => $request->state,
            'qr_code_hash'            => md5($bookingNumber . Str::random(10)),
            'payment_receipt'         => $receiptPath,
        ]);

        // Initialize Delivery Record for DSP
        $trackingNumber = 'TRK-' . rand(10000000, 99999999);
        \App\Models\Delivery::create([
            'booking_id'      => $booking->id,
            'dsp_id'          => $dspId,
            'tracking_number' => $trackingNumber,
            'stage'           => 'order_confirmed',
        ]);

        // Referral Commission Processing: Credit Referrer through Referral Config stages (increasing order)
        if ($user && $user->referred_by_id) {
            $referralService = app(\App\Services\ReferralCommissionService::class);
            $referralService->processReferralBooking($booking);

            if ($paymentType === 'full_payment') {
                $referralService->autoApprovePendingReferralsForBooking($booking);
            }
        }

        return redirect()->route('booking.receipt', $booking->booking_number)
            ->with('success', 'Booking created successfully!');
    }

    public function receipt($bookingNumber)
    {
        $booking = Booking::with(['product', 'user', 'transfers.fromUser', 'transfers.toUser'])
            ->where('booking_number', $bookingNumber)
            ->firstOrFail();

        return view('frontend.booking.receipt', compact('booking'));
    }

    public function payBalance(Request $request, $bookingNumber)
    {
        $query = Booking::where('booking_number', $bookingNumber);
        if (Auth::guard('web')->check()) {
            $query->where('user_id', Auth::guard('web')->id());
        }
        $booking = $query->firstOrFail();

        if ($booking->payment_status === 'fully_paid' || (float)$booking->balance_amount <= 0) {
            return redirect()->route('booking.receipt', $booking->booking_number)
                ->with('info', "Booking #{$booking->booking_number} is already fully paid!");
        }

        $request->validate([
            'payment_mode'    => 'nullable|in:full,emi,flexible',
            'emi_tenure'      => 'nullable|in:3,6,9,12',
            'custom_amount'   => 'nullable|numeric|min:1',
            'reference_no'    => 'nullable|string|max:100',
            'payment_receipt' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        $mode           = $request->input('payment_mode', 'full');
        $currentBalance = (float) $booking->balance_amount;
        $amountToPay    = 0.0;
        $tenure         = null;
        $emiMonthly     = (float) ($booking->emi_monthly_amount ?? 0);

        if ($mode === 'full') {
            $amountToPay = $currentBalance;
        } elseif ($mode === 'flexible') {
            $custom = (float) $request->input('custom_amount', 0);
            if ($custom <= 0) {
                return back()->with('error', 'Please enter a valid amount to pay.');
            }
            $amountToPay = min($custom, $currentBalance);
        } elseif ($mode === 'emi') {
            $tenure = (int) ($request->input('emi_tenure') ?: ($booking->emi_tenure_months ?: 6));
            if ($emiMonthly <= 0 || (int)$booking->emi_tenure_months !== $tenure) {
                $emiMonthly = round($currentBalance / $tenure, 2);
            }
            $amountToPay = min($emiMonthly, $currentBalance);
        }

        $newBalance  = max(0.0, round($currentBalance - $amountToPay, 2));
        $isFullyPaid = ($newBalance <= 0.0);

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
            'amount'         => $amountToPay,
            'tenure_months'  => $tenure,
            'installment_no' => $installmentNo,
            'reference_no'   => $request->input('reference_no') ?: ('UPI-' . rand(10000000, 99999999)),
            'receipt_file'   => $balanceReceiptPath,
            'paid_at'        => now()->toDateTimeString(),
        ];

        $updateData = [
            'balance_amount'           => $newBalance,
            'balance_payment_mode'     => $mode,
            'balance_payments_history' => $history,
        ];

        if ($balanceReceiptPath) {
            $updateData['payment_receipt'] = $balanceReceiptPath;
        }

        if ($mode === 'emi') {
            $updateData['emi_tenure_months']    = $tenure;
            $updateData['emi_monthly_amount']   = $emiMonthly;
            $updateData['emi_installments_paid'] = ($booking->emi_installments_paid ?? 0) + 1;
        }

        if ($isFullyPaid) {
            $updateData['payment_status'] = 'fully_paid';
            $updateData['booking_status'] = 'completed';
        } else {
            $updateData['payment_status'] = 'partial_paid';
        }

        $booking->update($updateData);

        if ($isFullyPaid) {
            $referralService = app(\App\Services\ReferralCommissionService::class);
            $referralService->autoApprovePendingReferralsForBooking($booking);
        }

        $msg = $isFullyPaid
            ? "Payment of ₹" . number_format($amountToPay, 2) . " completed successfully! Booking #{$booking->booking_number} is now FULLY PAID."
            : "Payment of ₹" . number_format($amountToPay, 2) . " processed successfully! Remaining balance: ₹" . number_format($newBalance, 2);

        return redirect()->route('booking.receipt', $booking->booking_number)->with('success', $msg);
    }
}
