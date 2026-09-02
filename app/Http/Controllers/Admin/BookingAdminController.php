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
