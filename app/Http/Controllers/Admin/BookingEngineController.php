<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingEngineSetting;
use Illuminate\Http\Request;

class BookingEngineController extends Controller
{
    public function settings()
    {
        $engineSettings = BookingEngineSetting::all()->pluck('value', 'key');
        return view('admin.booking_engine.settings', compact('engineSettings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'default_booking_percentage' => 'required|numeric|min:1|max:100',
            'balance_period_days' => 'required|integer|min:1|max:365',
            'transfer_allowed' => 'required|in:0,1',
            'reminder_schedule_days' => 'required|string',
            'booking_terms_version' => 'required|string',
        ]);

        $inputs = $request->only([
            'default_booking_percentage',
            'balance_period_days',
            'transfer_allowed',
            'reminder_schedule_days',
            'booking_terms_version',
            'expiry_handling'
        ]);

        foreach ($inputs as $key => $val) {
            BookingEngineSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        return back()->with('success', 'Booking Engine controls & rules updated successfully!');
    }
}
