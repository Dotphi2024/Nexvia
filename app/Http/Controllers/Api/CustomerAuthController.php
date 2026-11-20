<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SelfDealerWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Mail\OtpMail;
use App\Mail\CustomerWelcomeMail;

class CustomerAuthController extends Controller
{
    // -----------------------------------------------------------------------
    // STEP 1 — Register: save details only, no OTP sent here
    // -----------------------------------------------------------------------

    /**
     * POST /api/customer/register
     *
     * Accepts: name (or fullName), phone, password, password_confirmation (optional), email (optional)
     * Creates account with required password, removes OTP requirement, and returns auth token.
     */
    public function register(Request $request)
    {
        $name = trim($request->input('fullName') ?? $request->input('name') ?? '');
        if ($name !== '') {
            $request->merge(['name' => $name, 'fullName' => $name]);
        }

        $address = trim($request->input('address') ?? $request->input('full_address') ?? $request->input('street_address') ?? '');
        $pincode = trim($request->input('pincode') ?? $request->input('postal_code') ?? $request->input('postalCode') ?? $request->input('postcode') ?? '');
        $request->merge([
            'address' => $address,
            'pincode' => $pincode,
        ]);

        $validator = Validator::make($request->all(), [
            'fullName'              => 'nullable|string|max:255',
            'name'                  => 'required|string|max:255',
            'phone'                 => 'required|digits:10|unique:users,phone',
            'email'                 => 'nullable|email|max:255|unique:users,email',
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'nullable|string|same:password',
            'fcm_token'             => 'nullable|string',
            'referral_code'         => 'nullable|string',
            'referred_by'           => 'nullable|string',
            'address'               => 'required|string|min:3|max:500',
            'pincode'               => 'required|string|min:4|max:10',
            'city'                  => 'nullable|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'terms_accepted'        => 'nullable|boolean',
            'terms_version'         => 'nullable|string|max:20',
        ], [
            'name.required'              => 'Full name is required.',
            'phone.required'             => 'Phone number is required.',
            'phone.digits'               => 'Phone number must be exactly 10 digits.',
            'phone.unique'               => 'This phone number is already registered.',
            'email.unique'               => 'This email is already registered.',
            'password.required'          => 'Password is required to register.',
            'password.min'               => 'Password must be at least 6 characters long.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            'address.required'           => 'Address is required.',
            'pincode.required'           => 'Postal code / Pincode is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $password = $request->input('password');

            // 1. Check if registered using a referral code
            $referredById = null;
            $referrerInfo = null;
            $refInput = $request->input('referral_code')
                ?? $request->input('referralCode')
                ?? $request->input('refferal_code')
                ?? $request->input('referred_by')
                ?? $request->input('ref')
                ?? $request->input('referral code')
                ?? $request->input('refferal code');
            if (!empty($refInput)) {
                $refInput = strtoupper(trim($refInput));
                $referrer = Customer::where('referral_code', $refInput)->first();
                if ($referrer) {
                    $referredById = $referrer->id;
                    $referrerInfo = [
                        'id'            => $referrer->id,
                        'name'          => $referrer->name,
                        'referral_code' => $referrer->referral_code,
                    ];
                }
            }

            // 2. Generate unique referral code for this new customer
            do {
                $myReferralCode = 'NEX' . strtoupper(Str::random(6));
            } while (Customer::where('referral_code', $myReferralCode)->exists());

            // 3. T&C Acceptance record
            $termsAcceptedAt = ($request->boolean('terms_accepted') || $request->has('terms_version')) ? now() : null;
            $termsVersion    = $request->input('terms_version', 'v1.0');

            $customerData = [
                'name'                     => $name,
                'phone'                    => trim($request->phone),
                'email'                    => $request->email ? strtolower(trim($request->email)) : null,
                'address'                  => $address,
                'pincode'                  => $pincode,
                'city'                     => $request->city ?? null,
                'state'                    => $request->state ?? null,
                'dob'                      => $request->dob ?? null,
                'gst_number'               => $request->gst_number ?? null,
                'password'                 => $password, // auto-hashed via model cast
                'fcm_token'                => $request->fcm_token ?? null,
                'referral_code'            => $myReferralCode,
                'referred_by_id'           => $referredById,
                'is_self_dealer'           => false,
                'self_dealer_status'       => 'inactive',
                'wallet_balance'           => 0.00,
                'terms_accepted_at'        => $termsAcceptedAt,
                'terms_version'            => $termsVersion,
                'status'                   => 'active',
            ];

            $customer = Customer::create($customerData);

            // Also create default UserAddress for checkout and order fulfillment
            if (!empty($address)) {
                try {
                    \App\Models\UserAddress::create([
                        'user_id'    => $customer->id,
                        'name'       => $customer->name,
                        'phone'      => $customer->phone,
                        'street'     => $address,
                        'city'       => $customer->city ?: 'City',
                        'state'      => $customer->state ?: 'State',
                        'pincode'    => $pincode,
                        'is_default' => true,
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning("UserAddress auto-creation failed: " . $e->getMessage());
                }
            }

            // 4. Initialize Self Dealer Wallet
            SelfDealerWallet::firstOrCreate(['user_id' => $customer->id]);

            $token = $customer->generateApiToken();

            // Send Welcome notification on WhatsApp and Email if email provided
            $this->sendWelcomeWhatsApp($customer->phone, $password, $customer->name);
            if (!empty($customer->email)) {
                try {
                    Mail::to($customer->email)->send(new CustomerWelcomeMail($customer));
                } catch (\Throwable $e) {
                    \Log::warning("Customer welcome email failed: " . $e->getMessage());
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Registration successful.',
                'token'   => $token,
                'data'    => [
                    'id'                 => $customer->id,
                    'fullName'           => $customer->name,
                    'name'               => $customer->name,
                    'phone'              => $customer->phone,
                    'email'              => $customer->email,
                    'address'            => $customer->address,
                    'referral_code'      => $customer->referral_code,
                    'referral_url'       => url('/ref/' . $customer->referral_code),
                    'is_self_dealer'     => (bool) $customer->is_self_dealer,
                    'self_dealer_status' => $customer->self_dealer_status,
                    'wallet_balance'     => 0.00,
                    'city'               => $customer->city,
                    'state'              => $customer->state,
                    'pincode'            => $customer->pincode,
                    'fcm_token'          => $customer->fcm_token,
                    'referred_by'        => $referrerInfo,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Registration failed. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // STEP 2 — Login
    // -----------------------------------------------------------------------

    /**
     * POST /api/auth/login or /api/customer/login
     * Password-based direct login (OTP removed).
     * Accepts: phone (or email, emailOrPhone, mobile) & password
     */
    public function login(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $trimmedJson = [];
        if (is_array($bodyJson)) {
            foreach ($bodyJson as $key => $val) {
                $trimmedJson[trim($key)] = $val;
            }
        }

        $loginInput = trim(
            $request->input('phone') ??
            $request->input('emailOrPhone') ??
            $request->input('email') ??
            $request->input('mobile') ??
            $request->input('login') ??
            ($trimmedJson['phone'] ?? null) ??
            ($trimmedJson['emailOrPhone'] ?? null) ??
            ($trimmedJson['email'] ?? null) ??
            ($trimmedJson['mobile'] ?? null) ??
            ($trimmedJson['login'] ?? null) ?? ''
        );

        $password = $request->input('password') ?? ($trimmedJson['password'] ?? null);

        if (empty($loginInput)) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone number or email is required.',
            ], 422);
        }

        if (empty($password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password is required.',
            ], 422);
        }

        try {
            $customer = Customer::where('phone', $loginInput)
                ->orWhere('email', strtolower($loginInput))
                ->first();

            // Guard: user not found
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone/email. Please register first.',
                ], 404);
            }

            // Guard: deactivated account
            if ($customer->status !== 'active') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Your account is deactivated. Please contact support.',
                ], 403);
            }

            // Password verification
            if (!Hash::check($password, $customer->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid password credentials.',
                ], 401);
            }

            // Save FCM token if passed during login (supports snake_case and camelCase aliases)
            $fcmToken = $request->input('fcm_token')
                ?? $request->input('fcmToken')
                ?? $request->input('device_token')
                ?? $request->input('deviceToken')
                ?? ($trimmedJson['fcm_token'] ?? null)
                ?? ($trimmedJson['fcmToken'] ?? null)
                ?? ($trimmedJson['device_token'] ?? null)
                ?? ($trimmedJson['deviceToken'] ?? null);

            if (!empty($fcmToken)) {
                $customer->fcm_token = trim((string)$fcmToken);
                $customer->save();
            }

            // Generate authentication token and return customer data directly
            $token = $customer->generateApiToken();

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'token'   => $token,
                'data'    => [
                    'id'                 => $customer->id,
                    'fullName'           => $customer->name,
                    'name'               => $customer->name,
                    'phone'              => $customer->phone,
                    'email'              => $customer->email,
                    'referral_code'      => $customer->referral_code,
                    'referral_url'       => $customer->referral_code ? url('/ref/' . $customer->referral_code) : null,
                    'is_self_dealer'     => (bool) $customer->is_self_dealer,
                    'self_dealer_code'   => $customer->self_dealer_code,
                    'self_dealer_status' => $customer->self_dealer_status ?? 'inactive',
                    'wallet_balance'     => (float) ($customer->wallet_balance ?? 0),
                    'city'               => $customer->city,
                    'state'              => $customer->state,
                    'pincode'            => $customer->pincode,
                    'avatarUrl'          => $customer->profile_pic
                        ? asset('customer_pics/' . $customer->profile_pic)
                        : null,
                    'status'             => $customer->status,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Login failed. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/auth/send-otp
     * Accepts: phone, email (optional), type ('login' or 'register')
     */
    public function sendOtp(Request $request)
    {
        $type = strtolower(trim((string)$request->input('type', $request->input('purpose', 'login'))));

        if ($type === 'register' || $type === 'registration') {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|digits:10|unique:users,phone',
                'email' => 'nullable|email|max:255|unique:users,email',
            ], [
                'phone.required' => 'Phone number is required.',
                'phone.digits'   => 'Phone number must be exactly 10 digits.',
                'phone.unique'   => 'This phone number is already registered. Please login instead.',
                'email.unique'   => 'This email is already registered. Please login instead.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $phone = trim($request->phone);
            $email = $request->email ? strtolower(trim($request->email)) : null;
            $name  = trim($request->input('name', $request->input('fullName', 'Valued Customer')));

            $otp = (string) random_int(100000, 999999);
            Cache::put('reg_otp_' . $phone, [
                'otp'   => $otp,
                'email' => $email,
                'phone' => $phone,
            ], now()->addMinutes(10));

            $this->sendOtpWhatsApp($phone, $otp, $name);

            if (!empty($email)) {
                try {
                    Mail::to($email)->send(new OtpMail($name, $otp, 'registration'));
                } catch (\Throwable $e) {
                    \Log::warning("Registration OTP email failed: " . $e->getMessage());
                }
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Registration OTP sent successfully to your mobile and email.',
                'phone'     => $phone,
                'email'     => $email,
                'otp_debug' => config('app.debug') ? $otp : null,
                'data'      => [
                    'phone' => $phone,
                    'otp'   => config('app.debug') ? $otp : null,
                ],
            ], 200);
        }

        // Login flow
        $loginInput = trim($request->input('phone', $request->input('email', $request->input('login', ''))));
        if (empty($loginInput)) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone number or email is required.',
            ], 422);
        }

        try {
            $customer = Customer::where('phone', $loginInput)
                ->orWhere('email', strtolower($loginInput))
                ->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone number or email. Please register first.',
                ], 404);
            }

            $otp  = $customer->generateOtp();
            $this->sendOtpWhatsApp($customer->phone, $otp, $customer->name);
            if (!empty($customer->email)) {
                try {
                    Mail::to($customer->email)->send(new OtpMail($customer->name, $otp, 'login'));
                } catch (\Throwable $e) {
                    \Log::warning("Customer OTP email failed: " . $e->getMessage());
                }
            }

            return response()->json([
                'status'    => true,
                'message'   => 'OTP sent successfully to your mobile and email.',
                'phone'     => $customer->phone,
                'email'     => $customer->email,
                'otp_debug' => config('app.debug') ? $otp : null,
                'data'      => [
                    'phone' => $customer->phone,
                    'otp'   => config('app.debug') ? $otp : null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to send OTP.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/auth/verify-otp
     * Accepts: phone (or email), otpCode (or otp)
     */
    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otpCode') ?? $request->input('otp');
        $request->merge(['otp' => $otp]);

        $validator = Validator::make($request->all(), [
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => '6-digit OTP code is required.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $loginInput = trim($request->input('phone') ?? $request->input('email') ?? $request->input('login') ?? '');
        if (empty($loginInput)) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone number or email is required.',
            ], 422);
        }

        try {
            $customer = Customer::where('phone', $loginInput)
                ->orWhere('email', strtolower($loginInput))
                ->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone number or email.',
                ], 404);
            }

            $submittedOtp = trim((string)$request->otp);
            $validOtp = $customer->isOtpValid($submittedOtp);
            if (!$validOtp && config('app.debug') && $submittedOtp === '123456') {
                $validOtp = true;
            }

            if (!$validOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                ], 422);
            }

            // Clear OTP + mark phone verified
            $customer->markPhoneVerified();

            // Generate fresh API token
            $token = $customer->generateApiToken();

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified. Login successful!',
                'token'   => $token,
                'data'    => [
                    'id'                 => $customer->id,
                    'fullName'           => $customer->name,
                    'name'               => $customer->name,
                    'phone'              => $customer->phone,
                    'email'              => $customer->email,
                    'referral_code'      => $customer->referral_code,
                    'referral_url'       => $customer->referral_code ? url('/ref/' . $customer->referral_code) : null,
                    'is_self_dealer'     => (bool) $customer->is_self_dealer,
                    'self_dealer_code'   => $customer->self_dealer_code,
                    'self_dealer_status' => $customer->self_dealer_status ?? 'inactive',
                    'wallet_balance'     => (float) ($customer->wallet_balance ?? 0),
                    'avatarUrl'          => $customer->profile_pic
                        ? asset('customer_pics/' . $customer->profile_pic)
                        : null,
                    'status'             => $customer->status,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Verification failed. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/auth/refresh-token
     * Accepts: refreshToken (or Bearer token)
     */
    public function refreshToken(Request $request)
    {
        $token = $request->input('refreshToken')
            ?? $request->input('token')
            ?? $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Refresh token or Authorization Bearer token is required.',
            ], 400);
        }

        $customer = Customer::where('api_token', $token)->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired refresh token.',
            ], 401);
        }

        $newToken = $customer->generateApiToken();

        return response()->json([
            'status'       => true,
            'message'      => 'Token refreshed successfully.',
            'token'        => $newToken,
            'refreshToken' => $newToken,
        ], 200);
    }

    /**
     * POST /api/auth/forgot-password
     * Accepts: email
     */
    public function forgotPassword(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $email = trim(
            $request->input('email') ??
            ($bodyJson['email'] ?? null) ??
            $request->input('emailOrPhone') ??
            $request->input('login') ??
            $request->input('phone') ??
            ($bodyJson['phone'] ?? null) ?? ''
        );

        if (empty($email)) {
            return response()->json([
                'status'  => false,
                'message' => 'Registered email address is required.',
            ], 422);
        }

        try {
            $customer = Customer::where('email', strtolower($email))->first();
            if (!$customer && !str_contains($email, '@')) {
                $customer = Customer::where('phone', $email)->first();
            }

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            if (empty($customer->email)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No email address registered for this account.',
                ], 422);
            }

            $otp = $customer->generateOtp();
            try {
                Mail::to($customer->email)->send(new OtpMail($customer->name, $otp, 'password_reset'));
            } catch (\Throwable $e) {
                \Log::warning("Customer password reset email failed: " . $e->getMessage());
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Password reset OTP has been sent to your registered email address.',
                'email'     => $customer->email,
                'otp_debug' => config('app.debug') ? $otp : null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to process forgot password request.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/auth/reset-password or /api/customer/reset-password
     * Accepts: email, otp, password (or new_password), password_confirmation
     */
    public function resetPassword(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $input = trim(
            $request->input('email') ??
            ($bodyJson['email'] ?? null) ??
            $request->input('phone') ??
            $request->input('emailOrPhone') ??
            $request->input('mobile') ??
            $request->input('login') ??
            ($bodyJson['phone'] ?? null) ??
            ($bodyJson['emailOrPhone'] ?? null) ??
            ($bodyJson['mobile'] ?? null) ??
            ($bodyJson['login'] ?? null) ?? ''
        );

        $otp = trim(
            (string) (
                $request->input('otp') ??
                ($bodyJson['otp'] ?? '')
            )
        );

        $newPassword = $request->input('password')
            ?? $request->input('new_password')
            ?? ($bodyJson['password'] ?? null)
            ?? ($bodyJson['new_password'] ?? null);

        $confirmPassword = $request->input('password_confirmation')
            ?? $request->input('confirm_password')
            ?? ($bodyJson['password_confirmation'] ?? null)
            ?? ($bodyJson['confirm_password'] ?? null);

        if (empty($input)) {
            return response()->json([
                'status'  => false,
                'message' => 'Registered email address is required.',
            ], 422);
        }

        if (empty($otp)) {
            return response()->json([
                'status'  => false,
                'message' => 'OTP code is required.',
            ], 422);
        }

        if (empty($newPassword) || strlen($newPassword) < 6) {
            return response()->json([
                'status'  => false,
                'message' => 'New password must be at least 6 characters.',
            ], 422);
        }

        if (!empty($confirmPassword) && $newPassword !== $confirmPassword) {
            return response()->json([
                'status'  => false,
                'message' => 'Password confirmation does not match.',
            ], 422);
        }

        try {
            $customer = Customer::where('email', strtolower($input))->first();
            if (!$customer && !str_contains($input, '@')) {
                $customer = Customer::where('phone', $input)->first();
            }

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            $validOtp = $customer->isOtpValid($otp);
            if (!$validOtp && config('app.debug') && $otp === '123456') {
                $validOtp = true;
            }

            if (!$validOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                ], 422);
            }

            // Update password
            $customer->password = Hash::make($newPassword);
            $customer->markPhoneVerified();
            $token = $customer->generateApiToken();

            return response()->json([
                'status'  => true,
                'message' => 'Password has been reset successfully. You can now login with your new password.',
                'token'   => $token,
                'data'    => [
                    'id'       => $customer->id,
                    'name'     => $customer->name,
                    'fullName' => $customer->name,
                    'phone'    => $customer->phone,
                    'email'    => $customer->email,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to reset password. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/auth/change-password or /api/user/change-password
     * Accepts: old_password (or current_password), new_password, password_confirmation (or otp)
     */
    public function changePassword(Request $request)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();
        if (!$customer) {
            $token = $request->bearerToken() ?? $request->input('token');
            if ($token) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        if (!$customer) {
            $input = trim($request->input('phone') ?? $request->input('email') ?? '');
            if (!empty($input)) {
                $customer = Customer::where('phone', $input)->orWhere('email', strtolower($input))->first();
            }
        }

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated or user not found.',
            ], 401);
        }

        $oldPassword = $request->input('old_password') ?? $request->input('current_password');
        $newPassword = $request->input('new_password') ?? $request->input('password');
        $confirmPassword = $request->input('password_confirmation') ?? $request->input('confirm_password');
        $otp = trim((string)$request->input('otp'));

        if (empty($newPassword) || strlen($newPassword) < 6) {
            return response()->json([
                'status'  => false,
                'message' => 'New password must be at least 6 characters.',
            ], 422);
        }

        if (!empty($confirmPassword) && $newPassword !== $confirmPassword) {
            return response()->json([
                'status'  => false,
                'message' => 'Password confirmation does not match.',
            ], 422);
        }

        if (!empty($otp)) {
            $validOtp = $customer->isOtpValid($otp);
            if (!$validOtp && config('app.debug') && $otp === '123456') {
                $validOtp = true;
            }
            if (!$validOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid or expired OTP.',
                ], 422);
            }
            $customer->markPhoneVerified();
        } elseif (!empty($oldPassword)) {
            if (!Hash::check($oldPassword, $customer->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Current password is incorrect.',
                ], 422);
            }
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Either current password or verification OTP is required.',
            ], 422);
        }

        $customer->password = Hash::make($newPassword);
        $customer->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully.',
        ], 200);
    }

    /**
     * POST /api/auth/resend-otp
     * Accepts: phone
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $type = strtolower(trim((string)$request->input('type', $request->input('purpose', 'login'))));
            $phone = trim($request->phone);

            if ($type === 'register' || $type === 'registration') {
                $cached = Cache::get('reg_otp_' . $phone);
                $email = is_array($cached) ? ($cached['email'] ?? null) : null;
                $name  = is_array($cached) ? ($cached['name'] ?? 'Valued Customer') : 'Valued Customer';

                $otp = (string) random_int(100000, 999999);
                Cache::put('reg_otp_' . $phone, [
                    'otp'   => $otp,
                    'email' => $email,
                    'phone' => $phone,
                ], now()->addMinutes(10));

                $this->sendOtpWhatsApp($phone, $otp, $name);
                if (!empty($email)) {
                    try {
                        Mail::to($email)->send(new OtpMail($name, $otp, 'registration'));
                    } catch (\Throwable $e) {
                        \Log::warning("Customer resend registration OTP email failed: " . $e->getMessage());
                    }
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Registration OTP resent successfully.',
                    'phone'     => $phone,
                    'email'     => $email,
                    'otp_debug' => config('app.debug') ? $otp : null,
                    'data'      => [
                        'phone' => $phone,
                        'otp'   => config('app.debug') ? $otp : null,
                    ],
                ]);
            }

            $customer = Customer::where('phone', $phone)->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone number.',
                ], 404);
            }

            // Rate-limit: allow resend only after 1 minute from last OTP
            if ($customer->otp_expires_at && $customer->otp_expires_at->isFuture()) {
                $secondsLeft = (int) ceil(now()->diffInSeconds($customer->otp_expires_at->subMinutes(9), false));
                if ($secondsLeft > 0) {
                    return response()->json([
                        'status'       => false,
                        'message'      => "Please wait {$secondsLeft} seconds before requesting a new OTP.",
                        'retry_after'  => $secondsLeft,
                    ], 429);
                }
            }

            $otp  = $customer->generateOtp();
            $sent = $this->sendOtpWhatsApp($customer->phone, $otp, $customer->name);
            if (!empty($customer->email)) {
                try {
                    Mail::to($customer->email)->send(new OtpMail($customer->name, $otp, 'login'));
                } catch (\Throwable $e) {
                    \Log::warning("Customer resend OTP email failed: " . $e->getMessage());
                }
            }

            return response()->json([
                'status'    => true,
                'message'   => 'OTP resent to your WhatsApp number.',
                'phone'     => $customer->phone,
                'otp'       => config('app.debug') ? $otp : null,
                'otp_debug' => config('app.debug') ? $otp : null,
                'data'      => [
                    'phone' => $customer->phone,
                    'otp'   => config('app.debug') ? $otp : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to resend OTP. Please try again.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // Private helper — send OTP via WhatsApp (dotphi API)
    // -----------------------------------------------------------------------

    /**
     * @param  string $phone  10-digit number
     * @param  string $otp    6-digit OTP
     * @param  string $name   Customer name for personalised message
     */
    private function sendOtpWhatsApp(string $phone, string $otp, string $name): bool
    {
        try {
            $url = config('services.whatsapp.url');
            if (empty($url)) {
                return false;
            }

            $whatsappNumber = '91' . $phone; // India country code

            $message = "Hello {$name}! 👋\n\n"
                . "Your *Route-Mate* login OTP is:\n\n"
                . "🔐 *{$otp}*\n\n"
                . "This OTP is valid for *10 minutes*. Do not share it with anyone.\n\n"
                . "_Route-Mate Team_";

            $response = Http::timeout(3)->withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'token'   => config('services.whatsapp.token'),
                'to'      => $whatsappNumber,
                'message' => $message,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            \Log::warning('WhatsApp OTP send failed: ' . $e->getMessage(), ['phone' => $phone]);
            return false;
        }
    }

    /**
     * Send Welcome + Password notification via WhatsApp (dotphi API)
     */
    private function sendWelcomeWhatsApp(string $phone, string $password, string $name): bool
    {
        try {
            $url = config('services.whatsapp.url');
            if (empty($url)) {
                return false;
            }

            $whatsappNumber = '91' . $phone; // India country code

            $message = "Welcome to *Route-Mate*, {$name}! 🎉\n\n"
                . "Your account has been registered successfully.\n\n"
                . "📱 *Phone:* {$phone}\n"
                . "🔑 *Password:* `{$password}`\n\n"
                . "Please use these credentials to login.\n\n"
                . "_Route-Mate Team_";

            $response = Http::timeout(3)->withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
                'Content-Type'  => 'application/json',
            ])->post($url, [
                'token'   => config('services.whatsapp.token'),
                'to'      => $whatsappNumber,
                'message' => $message,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            \Log::warning('WhatsApp Welcome message send failed: ' . $e->getMessage(), ['phone' => $phone]);
            return false;
        }
    }

    /**
     * Get Customer Profile Details API
     *
     * GET /api/customer/profile/{id?} or GET /api/customer/profile?customer_id=1
     */
    /**
     * GET /api/user/profile or /api/customer/profile
     */
    public function profile(Request $request, $id = null)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
            ], 401);
        }

        try {

            $customerData = $customer->toArray();
            unset($customerData['otp_expires_at'], $customerData['phone_verified_at'], $customerData['email_verified_at'], $customerData['profile_pic']);

            // Get saved addresses
            $savedAddresses = \App\Models\UserAddress::where('user_id', $customer->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $defaultAddress = $savedAddresses->firstWhere('is_default', true) ?? $savedAddresses->first();

            $customerData['fullName']        = $customer->name;
            $customerData['address']         = $customer->address ?: ($defaultAddress?->street ?? null);
            $customerData['street']          = $customerData['address'];
            $customerData['city']            = $customer->city ?: ($defaultAddress?->city ?? null);
            $customerData['state']           = $customer->state ?: ($defaultAddress?->state ?? null);
            $customerData['pincode']         = $customer->pincode ?: ($defaultAddress?->pincode ?? null);
            $customerData['default_address'] = $defaultAddress;
            $customerData['saved_addresses'] = $savedAddresses;
            $customerData['avatarUrl']       = $customer->profile_pic
                ? asset('customer_pics/' . $customer->profile_pic)
                : null;

            // Sync with SelfDealerWallet
            $sdWallet = \App\Models\SelfDealerWallet::firstOrCreate(['user_id' => $customer->id]);
            $effectiveBalance = max((float)($customer->wallet_balance ?? 0), (float)($sdWallet->available_points ?? 0));
            $customerData['wallet_balance']             = $effectiveBalance;
            $customerData['available_incentive_points'] = (float)$sdWallet->available_points;

            $activationCredit = (float) \App\Models\WalletTransaction::where('user_id', $customer->id)
                ->where('type', 'credit')
                ->whereIn('source', ['self_dealer_activation'])
                ->where('status', 'available')
                ->sum('amount');

            if ($activationCredit <= 0) {
                $activationCredit = (float) \App\Models\WalletTransaction::where('user_id', $customer->id)
                    ->where('type', 'credit')
                    ->whereIn('source', ['self_dealer_activation'])
                    ->sum('amount');
            }

            if ($activationCredit <= 0 && $customer->activation_booking_id) {
                $activationCredit = (float) app(\App\Services\ReferralCommissionService::class)->getRequiredActivationStake($customer);
            }

            $referralCommission = (float) \App\Models\WalletTransaction::where('user_id', $customer->id)
                ->where('type', 'credit')
                ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
                ->where('status', 'available')
                ->sum('amount');

            $customerData['activation_credit']          = $activationCredit;
            $customerData['referral_credit']            = $referralCommission;
            $customerData['activation_credit_earned']   = $activationCredit;
            $customerData['referral_commission_earned'] = $referralCommission;

            $recentTxList = \App\Models\WalletTransaction::with(['booking', 'category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($t) => [
                    'id'                   => $t->id,
                    'transaction_type'     => $t->transaction_type ?? $t->source,
                    'amount'               => (float) $t->amount,
                    'type'                 => $t->type,
                    'status'               => $t->status,
                    'description'          => $t->description,
                    'booking_number'       => $t->booking?->booking_number,
                    'category_name'        => $t->category?->name,
                    'incentive_percentage' => (float) $t->incentive_percentage,
                    'created_at'           => $t->created_at ? $t->created_at->toIso8601String() : null,
                ]);

            $customerData['wallet'] = [
                'available_points'           => (float)$sdWallet->available_points,
                'pending_points'             => (float)$sdWallet->pending_points,
                'redeemed_points'            => (float)$sdWallet->redeemed_points,
                'total_earned'               => (float)$sdWallet->total_earned,
                'transactions'               => $recentTxList,
                'recent_transactions'        => $recentTxList,
            ];
            $customerData['transactions']        = $recentTxList;
            $customerData['recent_transactions'] = $recentTxList;

            return response()->json([
                'status'  => true,
                'message' => 'Profile fetched successfully.',
                'token'   => $customer->api_token,
                'data'    => $customerData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch customer profile.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * PUT /api/user/profile or POST /api/customer/update-profile
     * Accepts: fullName (or name), email, phone, address, street, city, state, pincode, avatarUrl (or profile_pic file/URL)
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
            ], 401);
        }

        $targetId = $customer->id;

        $fullName = $request->input('fullName') ?? $request->input('name');
        if ($fullName) {
            $request->merge(['name' => trim($fullName)]);
        }

        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $addressVal = $request->input('address')
            ?? $request->input('street')
            ?? $request->input('address_line')
            ?? ($bodyJson['address'] ?? null)
            ?? ($bodyJson['street'] ?? null)
            ?? ($bodyJson['address_line'] ?? null);

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|nullable|email|max:255|unique:users,email,' . $targetId,
            'phone'       => 'sometimes|digits:10|unique:users,phone,' . $targetId,
            'address'     => 'nullable|string',
            'profile_pic' => 'nullable',
            'avatarUrl'   => 'nullable',
            'avatar'      => 'nullable',
            'image'       => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {

            if ($addressVal) {
                $customer->address = $addressVal;
            }

            // Update text fields if provided
            foreach (['name', 'phone', 'address', 'pincode', 'city', 'state', 'dob', 'gst_number'] as $field) {
                if ($request->has($field) && !empty($request->$field)) {
                    $customer->$field = $request->$field;
                }
            }

            // Sync email across users table and all bookings
            if ($request->has('email') && !empty($request->email)) {
                $newEmail = strtolower(trim($request->email));
                if ($customer->email !== $newEmail) {
                    $customer->email = $newEmail;
                    try {
                        \App\Models\Booking::where('user_id', $customer->id)
                            ->update(['customer_email' => $newEmail]);
                    } catch (\Throwable $e) {
                        \Log::warning("Syncing booking email failed: " . $e->getMessage());
                    }
                }
            }

            // Sync name across users table and all bookings
            if ($request->has('name') && !empty($request->name)) {
                $newName = trim($request->name);
                if ($customer->name !== $newName) {
                    $customer->name = $newName;
                    try {
                        \App\Models\Booking::where('user_id', $customer->id)
                            ->update(['customer_name' => $newName]);
                    } catch (\Throwable $e) {
                        \Log::warning("Syncing booking name failed: " . $e->getMessage());
                    }
                }
            }

            // Sync with default UserAddress if address provided
            if (!empty($customer->address)) {
                $defaultAddr = \App\Models\UserAddress::where('user_id', $customer->id)->where('is_default', true)->first();
                if ($defaultAddr) {
                    $defaultAddr->update([
                        'name'    => $customer->name,
                        'phone'   => $customer->phone,
                        'street'  => $customer->address,
                        'city'    => $customer->city ?: $defaultAddr->city,
                        'state'   => $customer->state ?: $defaultAddr->state,
                        'pincode' => $customer->pincode ?: $defaultAddr->pincode,
                    ]);
                } else {
                    \App\Models\UserAddress::create([
                        'user_id'    => $customer->id,
                        'name'       => $customer->name,
                        'phone'      => $customer->phone,
                        'street'     => $customer->address,
                        'city'       => $customer->city ?: 'Mumbai',
                        'state'      => $customer->state ?: 'Maharashtra',
                        'pincode'    => $customer->pincode ?: '400001',
                        'is_default' => true,
                    ]);
                }
            }

            // Handle file upload under any field name (avatarUrl, profile_pic, avatar, image, etc.)
            $allFiles = $request->allFiles();
            $picFile  = $request->file('profile_pic')
                ?? $request->file('avatarUrl')
                ?? $request->file('avatar')
                ?? $request->file('image')
                ?? collect($allFiles)->first();

            if ($picFile && $picFile->isValid()) {
                if (!file_exists(public_path('customer_pics'))) {
                    mkdir(public_path('customer_pics'), 0755, true);
                }
                if ($customer->profile_pic && file_exists(public_path('customer_pics/' . $customer->profile_pic))) {
                    @unlink(public_path('customer_pics/' . $customer->profile_pic));
                }
                $filename = 'customer_pic_' . time() . '_' . uniqid() . '.' . $picFile->getClientOriginalExtension();
                $picFile->move(public_path('customer_pics'), $filename);
                $customer->profile_pic = $filename;
            } elseif ($request->has('avatarUrl') && is_string($request->avatarUrl) && !empty($request->avatarUrl)) {
                $customer->profile_pic = $request->avatarUrl;
            }

            $customer->save();

            $customerData = $customer->fresh()->toArray();
            unset($customerData['otp_expires_at'], $customerData['phone_verified_at'], $customerData['email_verified_at'], $customerData['profile_pic']);

            $customerData['fullName']  = $customer->name;
            $customerData['avatarUrl'] = $customer->profile_pic
                ? (str_starts_with($customer->profile_pic, 'http') ? $customer->profile_pic : asset('customer_pics/' . $customer->profile_pic))
                : null;

            return response()->json([
                'status'  => true,
                'message' => 'Profile updated successfully.',
                'token'   => $customer->api_token,
                'data'    => $customerData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update profile.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Save/Update Customer FCM Device Token for Push Notifications
     *
     * POST /api/customer/fcm-token
     */
    public function updateFcmToken(Request $request)
    {
        $rawBody = json_decode($request->getContent(), true) ?? [];
        $tokenInput = $request->input('fcm_token')
            ?? $request->input('fcmToken')
            ?? $request->input('device_token')
            ?? $request->input('deviceToken')
            ?? ($rawBody['fcm_token'] ?? null)
            ?? ($rawBody['fcmToken'] ?? null)
            ?? ($rawBody['device_token'] ?? null)
            ?? ($rawBody['deviceToken'] ?? null);

        if (!empty($tokenInput)) {
            $request->merge(['fcm_token' => trim((string)$tokenInput)]);
        }

        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $customer = $request->get('authenticated_customer') ?? $request->user();

            if (!$customer) {
                $bodyJson = json_decode($request->getContent(), true) ?? [];
                $token = $request->bearerToken()
                    ?? $request->input('api_token')
                    ?? $request->input('token')
                    ?? $request->header('api_token')
                    ?? $request->header('token')
                    ?? ($bodyJson['api_token'] ?? null)
                    ?? ($bodyJson['token'] ?? null);

                if (!empty($token)) {
                    $customer = Customer::where('api_token', $token)->first();
                }
            }

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $customer->fcm_token = $request->fcm_token;
            if ($request->has('latitude')) {
                $customer->current_latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $customer->current_longitude = $request->longitude;
            }
            $customer->save();

            return response()->json([
                'status'  => true,
                'message' => 'FCM token updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update FCM token.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Customer Logout API
     *
     * POST /api/customer/logout or /api/auth/logout or /api/logout
     * Strictly requires authorization token.
     */
    public function logout(Request $request)
    {
        try {
            $customer = $request->get('authenticated_customer') ?? $request->user();

            if (!$customer) {
                $bodyJson = json_decode($request->getContent(), true) ?? [];
                $token = $request->bearerToken()
                    ?? $request->input('api_token')
                    ?? $request->input('token')
                    ?? $request->header('api_token')
                    ?? $request->header('token')
                    ?? ($bodyJson['api_token'] ?? null)
                    ?? ($bodyJson['token'] ?? null);

                if (!empty($token)) {
                    $customer = Customer::where('api_token', $token)->first();
                }
            }

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required to logout.',
                ], 401);
            }

            // Invalidate/clear active api_token
            $customer->api_token = null;
            $customer->save();

            // Clear active guards
            try {
                auth()->guard('customer')->logout();
            } catch (\Throwable $e) {}

            try {
                auth()->guard('web')->logout();
            } catch (\Throwable $e) {}

            return response()->json([
                'status'  => true,
                'message' => 'User logged out successfully.',
                'data'    => [
                    'id'    => $customer->id,
                    'name'  => $customer->name,
                    'phone' => $customer->phone,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to logout.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/notifications
     */
    public function getNotifications(Request $request)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();
        if (!$customer) {
            $token = $request->bearerToken() ?? $request->input('api_token') ?? $request->input('token');
            if ($token) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        if (!$customer) {
            $userId = $request->input('user_id') ?? $request->input('userId');
            if ($userId) {
                $customer = Customer::find($userId);
            }
        }

        $notifications = [];

        if ($customer) {
            // Persisted notifications from DB
            $dbNotifications = \DB::table('notifications')
                ->where('notifiable_type', Customer::class)
                ->where('notifiable_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($dbNotifications as $item) {
                $payload = json_decode($item->data ?? '{}', true) ?: [];
                $createdAtCarbon = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : now();

                $notifications[] = [
                    'id'         => (string) $item->id,
                    'title'      => $payload['title'] ?? 'Notification',
                    'message'    => $payload['message'] ?? ($payload['body'] ?? ''),
                    'type'       => $payload['type'] ?? 'system',
                    'is_read'    => !empty($item->read_at),
                    'created_at' => $createdAtCarbon->toIso8601String(),
                    'time_ago'   => $createdAtCarbon->diffForHumans(),
                    'data'       => $payload,
                ];
            }

            // Synthesize dynamic notifications based on real bookings & wallet
            if (count($notifications) < 3) {
                $bookings = \App\Models\Booking::where('user_id', $customer->id)
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

                foreach ($bookings as $b) {
                    $orderNo = $b->booking_id ?? 'NEX-' . str_pad($b->id, 5, '0', STR_PAD_LEFT);
                    $balance = (float)($b->balance_due ?? 0);
                    $daysRemaining = 60;
                    if ($b->created_at) {
                        $daysPassed = (int) $b->created_at->diffInDays(now());
                        $daysRemaining = max(0, 60 - $daysPassed);
                    }

                    if ($balance > 0) {
                        $notifications[] = [
                            'id'         => 'booking-balance-' . $b->id,
                            'title'      => 'Flexi-Booking: Balance Due Alert',
                            'message'    => "Order #{$orderNo}: {$daysRemaining} days remaining to pay balance of ₹" . number_format($balance, 2) . ".",
                            'type'       => 'order',
                            'is_read'    => false,
                            'created_at' => $b->created_at ? $b->created_at->toIso8601String() : now()->toIso8601String(),
                            'data'       => [
                                'booking_id' => $b->id,
                                'order_no'   => $orderNo,
                                'route'      => '/order-tracking',
                            ],
                        ];
                    }

                    $status = strtolower($b->status ?? 'confirmed');
                    $statusTitle = $status === 'delivered' ? 'Order Delivered Successfully' : ($status === 'dispatched' ? 'Order Dispatched with Live Tracking' : 'Booking Confirmed - 20% Advance Received');
                    $statusMsg = $status === 'delivered' 
                        ? "Order #{$orderNo} has been delivered. Your Free Technician Installation is ready to schedule."
                        : ($status === 'dispatched' 
                            ? "Order #{$orderNo} is in transit. Tracking TRK: " . ($b->tracking_number ?? 'TRK-' . rand(100000, 999999))
                            : "Your 20% advance for Order #{$orderNo} is verified. Flexi-guarantee active.");

                    $notifications[] = [
                        'id'         => 'booking-status-' . $b->id,
                        'title'      => $statusTitle,
                        'message'    => $statusMsg,
                        'type'       => 'order',
                        'is_read'    => false,
                        'created_at' => $b->created_at ? $b->created_at->toIso8601String() : now()->toIso8601String(),
                        'data'       => [
                            'booking_id' => $b->id,
                            'order_no'   => $orderNo,
                            'route'      => '/order-tracking',
                        ],
                    ];
                }

                // Credit / Dealer Notification
                $walletBalance = (float)($customer->wallet_balance ?? 0);
                if ($walletBalance > 0 || $customer->is_self_dealer) {
                    $notifications[] = [
                        'id'         => 'credit-wallet-balance',
                        'title'      => 'Self-Dealer & Credit Hub',
                        'message'    => "You have ₹" . number_format($walletBalance, 2) . " credits available in your Nexvia wallet.",
                        'type'       => 'credit',
                        'is_read'    => false,
                        'created_at' => now()->subMinutes(10)->toIso8601String(),
                        'time_ago'   => now()->subMinutes(10)->diffForHumans(),
                        'data'       => [
                            'route' => '/dealer-hub',
                        ],
                    ];
                }

                // Welcome / Service Notification
                $notifications[] = [
                    'id'         => 'welcome-service-notice',
                    'title'      => 'Free Installation & Warranties Included',
                    'message'    => 'All major Nexvia equipment comes with free expert technician installation & comprehensive warranty.',
                    'type'       => 'service',
                    'is_read'    => false,
                    'created_at' => now()->subDay()->toIso8601String(),
                    'data'       => [
                        'route' => '/warranties',
                    ],
                ];
            }
        }

        $unreadCount = count(array_filter($notifications, fn($n) => empty($n['is_read'])));

        return response()->json([
            'status'       => true,
            'unread_count' => $unreadCount,
            'data'         => $notifications,
        ], 200);
    }

    /**
     * POST /api/customer/notifications/mark-read
     */
    public function markNotificationRead(Request $request)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();
        if (!$customer) {
            $token = $request->bearerToken() ?? $request->input('api_token') ?? $request->input('token');
            if ($token) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        $id = $request->input('id');
        $all = $request->boolean('all');

        if ($customer) {
            $query = \DB::table('notifications')
                ->where('notifiable_type', Customer::class)
                ->where('notifiable_id', $customer->id);

            if (!$all && !empty($id)) {
                $query->where('id', $id);
            }

            $query->update(['read_at' => now()]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notifications marked as read.',
        ]);
    }

    /**
     * POST /api/customer/test-notification
     * Accepts: customer_id, email, or query (e.g. 'dhuraj'), title, message/body
     */
    public function sendTestNotification(Request $request)
    {
        $query = $request->input('customer_id') 
            ?? $request->input('user_id') 
            ?? $request->input('email') 
            ?? $request->input('query') 
            ?? $request->input('name') 
            ?? 'dhuraj';

        $customer = is_numeric($query)
            ? Customer::find($query)
            : Customer::where('email', 'LIKE', "%{$query}%")
                ->orWhere('name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%")
                ->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => "Customer not found for identifier: {$query}",
            ], 404);
        }

        $title   = $request->input('title') ?? "🎉 Test Notification for {$customer->name}";
        $message = $request->input('message') ?? $request->input('body') ?? "Hello {$customer->name}! Your Nexvia notifications and order alerts are now fully active.";
        $type    = $request->input('type') ?? 'system';

        // 1. Insert into database notifications
        $notificationId = (string) \Illuminate\Support\Str::uuid();
        \DB::table('notifications')->insert([
            'id'              => $notificationId,
            'type'            => 'App\Notifications\CustomerAlertNotification',
            'notifiable_type' => Customer::class,
            'notifiable_id'   => $customer->id,
            'data'            => json_encode([
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'route'   => $request->input('route') ?? '/notifications',
            ]),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 2. Push notification via FCM if token exists
        $fcmSent = false;
        if (!empty($customer->fcm_token)) {
            $fcmSent = \App\Services\FcmService::sendNotification(
                $customer->fcm_token,
                $title,
                $message,
                ['type' => $type, 'route' => '/notifications']
            );
            $fcmNote = $fcmSent ? 'FCM push sent successfully to device token.' : 'FCM push dispatch failed (check logs).';
        } else {
            $fcmNote = 'Device FCM token is not registered yet. In-app notification created and immediately visible in the mobile app notification center.';
        }

        return response()->json([
            'status'              => true,
            'message'             => 'Notification created successfully.',
            'notification_id'     => $notificationId,
            'customer'            => [
                'id'        => $customer->id,
                'name'      => $customer->name,
                'email'     => $customer->email,
                'has_token' => !empty($customer->fcm_token),
            ],
            'fcm_push_dispatched' => $fcmSent,
            'fcm_note'            => $fcmNote,
        ]);
    }
}



