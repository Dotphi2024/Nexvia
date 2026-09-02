<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingTransferController extends Controller
{
    public function initiateTransfer(Request $request, $bookingNumber)
    {
        $request->validate([
            'to_name' => 'required|string|max:255',
            'to_phone' => 'required|string|max:20',
        ]);

        $booking = Booking::where('booking_number', $bookingNumber)
            ->where('user_id', Auth::guard('web')->id())
            ->firstOrFail();

        if ($booking->payment_status === 'fully_paid') {
            return back()->with('error', 'Fully paid bookings cannot be transferred.');
        }

        $recipient = Customer::where('phone', $request->to_phone)->first();

        // Generate Transfer OTP
        $transferOtp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $transfer = BookingTransfer::create([
            'booking_id' => $booking->id,
            'from_user_id' => Auth::guard('web')->id(),
            'to_name' => $request->to_name,
            'to_phone' => $request->to_phone,
            'to_user_id' => $recipient ? $recipient->id : null,
            'transfer_otp' => $transferOtp,
            'transfer_otp_expires_at' => Carbon::now()->addMinutes(30),
            'status' => 'pending',
        ]);

        return redirect()->route('booking.transfer.confirm', $transfer->id)
            ->with('info', "Transfer OTP sent to {$request->to_phone}. (Demo OTP: {$transferOtp})");
    }

    public function confirmTransferView($transferId)
    {
        $transfer = BookingTransfer::with(['booking.product', 'fromUser'])->findOrFail($transferId);
        return view('frontend.booking.transfer_confirm', compact('transfer'));
    }

    public function processTransferConfirmation(Request $request, $transferId)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $transfer = BookingTransfer::with('booking')->findOrFail($transferId);

        if ($transfer->transfer_otp !== $request->otp || Carbon::now()->isAfter($transfer->transfer_otp_expires_at)) {
            return back()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        // Find or create recipient customer profile
        $recipient = Customer::where('phone', $transfer->to_phone)->first();
        if (!$recipient) {
            $recipient = Customer::create([
                'name' => $transfer->to_name,
                'phone' => $transfer->to_phone,
                'email' => Str::slug($transfer->to_name) . rand(100, 999) . '@nexvia.com',
                'status' => 'active',
            ]);
        }

        // Transfer ownership of booking
        $booking = $transfer->booking;
        $booking->update([
            'user_id' => $recipient->id,
            'customer_name' => $recipient->name,
            'customer_phone' => $recipient->phone,
            'transfer_status' => 'transferred',
        ]);

        $transfer->update([
            'to_user_id' => $recipient->id,
            'status' => 'completed',
            'transferred_at' => Carbon::now(),
        ]);

        return redirect()->route('booking.receipt', $booking->booking_number)
            ->with('success', "Booking Receipt successfully transferred to {$recipient->name} ({$recipient->phone})!");
    }
}
