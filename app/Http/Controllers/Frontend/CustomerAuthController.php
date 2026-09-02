<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $ref = $request->query('ref');
        if ($ref) {
            session(['referral_code' => $ref]);
        }
        return view('frontend.auth.login', compact('ref'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();
        if (!$customer) {
            $referredById = null;
            $sessionRef = session('referral_code') ?? $request->referral_code;
            if ($sessionRef) {
                $referrer = Customer::where('referral_code', $sessionRef)->first();
                if ($referrer) {
                    $referredById = $referrer->id;
                }
            }

            $customer = Customer::create([
                'name' => 'User ' . substr($request->phone, -4),
                'phone' => $request->phone,
                'email' => 'user_' . $request->phone . '@nexvia.com',
                'password' => Hash::make(Str::random(12)),
                'referred_by_id' => $referredById,
                'status' => 'active',
            ]);
        }

        $otp = $customer->generateOtp();

        return redirect()->route('customer.verify.otp.view', ['phone' => $customer->phone])
            ->with('info', "OTP sent to {$customer->phone}. (Demo OTP: {$otp})");
    }

    public function showVerifyOtp(Request $request)
    {
        $phone = $request->query('phone');
        return view('frontend.auth.verify_otp', compact('phone'));
    }

    public function processVerifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer || !$customer->isOtpValid($request->otp)) {
            return back()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        $customer->markPhoneVerified();
        Auth::guard('web')->login($customer);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Logged in successfully!');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}
