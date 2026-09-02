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
        $paymentType = $request->query('type', 'booking_20');
        $user = Auth::guard('web')->user();

        return view('frontend.booking.checkout', compact('product', 'paymentType', 'user'));
    }

    public function processCheckout(Request $request, $slug)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'pincode' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'payment_type' => 'required|in:booking_20,full_payment',
            'non_refundable_terms' => 'required_if:payment_type,booking_20|accepted',
        ]);

        $product = Product::with('category')->where('slug', $slug)->firstOrFail();
        $user = Auth::guard('web')->user();

        $bookingNumber = 'NEX-' . date('Y') . '-' . strtoupper(Str::random(6));
        $bookingDate = Carbon::today();
        $balanceDueDate = Carbon::today()->addDays(60);

        if ($request->payment_type === 'full_payment') {
            $rawPayable = $product->mrp;
            $bookingAmount = $product->mrp;
            $balanceAmount = 0.00;
            $paymentStatus = 'fully_paid';
            $bookingStatus = 'completed';
        } else {
            $rawPayable = $product->booking_amount;
            $bookingAmount = $product->booking_amount;
            $balanceAmount = $product->balance_amount;
            $paymentStatus = 'paid';
            $bookingStatus = 'booked';
        }

        // Apply Product Credit Wallet redemption if requested
        $appliedWalletDiscount = 0.00;
        if ($request->has('use_wallet') && $user->wallet_balance > 0) {
            $appliedWalletDiscount = min($user->wallet_balance, $bookingAmount);
            $bookingAmount = $bookingAmount - $appliedWalletDiscount;

            // Deduct wallet balance
            $user->decrement('wallet_balance', $appliedWalletDiscount);

            // Record Debit Transaction
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $appliedWalletDiscount,
                'type' => 'debit',
                'source' => 'booking_redemption',
                'description' => "Redeemed Product Credit for Receipt #{$bookingNumber}",
            ]);
        }

        $booking = Booking::create([
            'booking_number' => $bookingNumber,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'model_code' => $product->model_code,
            'mrp' => $product->mrp,
            'booking_amount' => $bookingAmount,
            'balance_amount' => $balanceAmount,
            'booking_date' => $bookingDate,
            'balance_due_date' => $balanceDueDate,
            'payment_type' => $request->payment_type,
            'payment_status' => $paymentStatus,
            'booking_status' => $bookingStatus,
            'transfer_status' => 'original',
            'non_refundable_accepted' => true,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $request->shipping_address,
            'pincode' => $request->pincode,
            'city' => $request->city,
            'state' => $request->state,
            'qr_code_hash' => md5($bookingNumber . Str::random(10)),
        ]);

        // Referral Commission Processing: Credit Referrer
        if ($user->referred_by_id) {
            $referrer = Customer::find($user->referred_by_id);
            if ($referrer) {
                $commissionPct = $product->category->commission_percentage ?? 3.00;
                $commissionEarned = $rawPayable * ($commissionPct / 100);

                if ($commissionEarned > 0) {
                    $referrer->increment('wallet_balance', $commissionEarned);

                    WalletTransaction::create([
                        'user_id' => $referrer->id,
                        'amount' => $commissionEarned,
                        'type' => 'credit',
                        'source' => 'referral_commission',
                        'booking_id' => $booking->id,
                        'description' => "Earned {$commissionPct}% referral credit from {$user->name}'s booking #{$bookingNumber}",
                    ]);
                }
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
        $booking = Booking::where('booking_number', $bookingNumber)
            ->where('user_id', Auth::guard('web')->id())
            ->firstOrFail();

        $booking->update([
            'payment_status' => 'fully_paid',
            'booking_status' => 'completed',
            'balance_amount' => 0.00,
        ]);

        return redirect()->route('customer.dashboard')
            ->with('success', "Balance payment of ₹" . number_format($booking->balance_amount, 2) . " completed successfully for Receipt #{$booking->booking_number}!");
    }
}
