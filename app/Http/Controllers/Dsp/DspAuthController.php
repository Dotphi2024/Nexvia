<?php

namespace App\Http\Controllers\Dsp;

use App\Http\Controllers\Controller;
use App\Models\DspApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DspAuthController extends Controller
{
    /**
     * Show DSP Partner login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('dsp')->check()) {
            return redirect()->route('dsp.dashboard');
        }

        return view('dsp.auth.login');
    }

    /**
     * Process DSP Login using Mobile and Password
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => 'required|string',
            'password' => 'required|string',
        ]);

        $mobile = trim($request->mobile);
        $password = $request->password;

        $dsp = DspApplication::where('mobile', $mobile)
            ->orWhere('whatsapp', $mobile)
            ->first();

        if (!$dsp) {
            return back()->withInput($request->only('mobile'))->withErrors([
                'mobile' => 'No DSP partner application registered with this mobile number.',
            ]);
        }

        // Check if password matches
        $passwordValid = false;
        if (!empty($dsp->password)) {
            $passwordValid = Hash::check($password, $dsp->password);
        } else {
            // Default initial temporary password is set to 'password' or last 6 digits of mobile for first-time access
            $defaultPass = 'dsp@123';
            if ($password === $defaultPass || $password === substr($dsp->mobile, -6)) {
                $dsp->password = Hash::make($password);
                $dsp->save();
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            return back()->withInput($request->only('mobile'))->withErrors([
                'password' => 'Incorrect password. Please verify your credentials or contact support.',
            ]);
        }

        if ($dsp->status === 'rejected') {
            return back()->withInput($request->only('mobile'))->withErrors([
                'mobile' => 'Your DSP partner application is not active. Status: Rejected.',
            ]);
        }

        Auth::guard('dsp')->login($dsp, $request->filled('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dsp.dashboard'))
            ->with('success', 'Welcome back, ' . ($dsp->applicant_name ?: $dsp->business_name) . '!');
    }

    /**
     * DSP Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('dsp')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dsp.login')->with('success', 'You have been logged out securely.');
    }
}
