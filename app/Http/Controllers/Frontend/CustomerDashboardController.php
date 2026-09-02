<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();
        $user->load(['referrals', 'walletTransactions' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);

        $bookings = Booking::with('product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $transfers = BookingTransfer::with(['booking.product', 'toUser'])
            ->where('from_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $walletTransactions = $user->walletTransactions;
        $totalReferralsCount = $user->referrals->count();
        $totalCreditsEarned = $walletTransactions->where('type', 'credit')->sum('amount');

        return view('frontend.customer.dashboard', compact(
            'user',
            'bookings',
            'transfers',
            'walletTransactions',
            'totalReferralsCount',
            'totalCreditsEarned'
        ));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::guard('web')->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'pincode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'gst_number' => 'nullable|string|max:50',
        ]);

        $user->update($request->only(['name', 'email', 'pincode', 'city', 'state', 'dob', 'gst_number']));

        return back()->with('success', 'Profile details updated successfully!');
    }
}
