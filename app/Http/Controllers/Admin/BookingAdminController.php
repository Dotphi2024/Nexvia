<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use Illuminate\Http\Request;

class BookingAdminController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['product', 'user'])->latest()->paginate(20);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with(['product', 'user', 'transfers.fromUser', 'transfers.toUser'])->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

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

        $booking->update([
            'booking_status' => $request->booking_status,
            'payment_status' => $request->payment_status,
        ]);

        return back()->with('success', 'Booking status updated successfully!');
    }
}
