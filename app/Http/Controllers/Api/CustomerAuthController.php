<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class CustomerAuthController extends Controller
{
    // -----------------------------------------------------------------------
    // STEP 1 — Register: save details only, no OTP sent here
    // -----------------------------------------------------------------------

    /**
     * POST /api/customer/register
     *
     * Accepts: name, phone, email (optional), password, password_confirmation
     * Just creates the account and returns success.
     * OTP is sent only at login time.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'phone'       => 'required|digits:10|unique:users,phone',
            'email'       => 'nullable|email|max:255|unique:users,email',
            'fcm_token'   => 'nullable|string',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'profile_pic' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $defaultPassword = $request->password ?? '12345678';

            $customerData = [
                'name'              => trim($request->name),
                'phone'             => trim($request->phone),
                'email'             => $request->email ? strtolower(trim($request->email)) : null,
                'password'          => $defaultPassword,  // auto-hashed via cast
                'fcm_token'         => $request->fcm_token ?? null,
                'current_latitude'  => $request->latitude ?? null,
                'current_longitude' => $request->longitude ?? null,
                'status'            => 'active',           // account is active immediately
            ];

            // Handle optional profile_pic file upload
            $allFiles = $request->allFiles();
            $picFile  = $request->file('profile_pic')
                ?? collect($allFiles)->first(fn($v, $k) => str_starts_with($k, 'profile_pic'));

            if ($picFile) {
                if (!file_exists(public_path('customer_pics'))) {
                    mkdir(public_path('customer_pics'), 0755, true);
                }
                $filename = 'customer_pic_' . time() . '_' . uniqid() . '.' . $picFile->getClientOriginalExtension();
                $picFile->move(public_path('customer_pics'), $filename);
                $customerData['profile_pic'] = $filename;
            }

            $customer = Customer::create($customerData);
            $token = $customer->generateApiToken();

            // Send Welcome + Password notification on WhatsApp
            $this->sendWelcomeWhatsApp($customer->phone, $defaultPassword, $customer->name);

            return response()->json([
                'status'  => true,
                'message' => 'Registration successful.',
                'token'   => $token,
                'data'    => [
                    'id'              => $customer->id,
                    'name'            => $customer->name,
                    'phone'           => $customer->phone,
                    'email'           => $customer->email,
                    'fcm_token'       => $customer->fcm_token,
                    'profile_pic_url' => $customer->profile_pic
                        ? asset('customer_pics/' . $customer->profile_pic)
                        : null,
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
     * POST /api/customer/login
     *
     * Accepts: phone (or email), password, optional: fcm_token, latitude, longitude
     */
    public function login(Request $request)
    {
        $loginInput = trim(
            $request->input('phone') ??
            $request->input('email') ??
            $request->input('login') ?? ''
        );

        $validator = Validator::make($request->all(), [
            'password'  => 'required|string',
            'fcm_token' => 'nullable|string',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if (empty($loginInput) || $validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Phone/email and password are required.',
            ], 422);
        }

        try {
            $customer = Customer::where('phone', $loginInput)
                ->orWhere('email', strtolower($loginInput))
                ->first();

            // Guard: wrong credentials
            if (!$customer || !Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid phone/email or password.',
                ], 401);
            }

            // Guard: deactivated account
            if ($customer->status !== 'active') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Your account is deactivated. Please contact support.',
                ], 403);
            }

            // Save FCM token and location if passed during login
            if ($request->has('fcm_token')) {
                $customer->fcm_token = $request->fcm_token;
            }
            if ($request->has('latitude')) {
                $customer->current_latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $customer->current_longitude = $request->longitude;
            }
            $customer->save();

            // Credentials are valid → generate token and return customer data directly
            $token = $customer->generateApiToken();

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'token'   => $token,
                'data'    => [
                    'id'              => $customer->id,
                    'name'            => $customer->name,
                    'phone'           => $customer->phone,
                    'email'           => $customer->email,
                    'fcm_token'       => $customer->fcm_token,
                    'profile_pic_url' => $customer->profile_pic
                        ? asset('customer_pics/' . $customer->profile_pic)
                        : null,
                    'status'          => $customer->status,
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

    // -----------------------------------------------------------------------
    // STEP 3 — Verify OTP: confirm OTP + return api_token
    // -----------------------------------------------------------------------

    /**
     * POST /api/customer/verify-otp
     *
     * Accepts: phone, otp
     * Verifies OTP → marks phone verified → returns api_token for all future requests.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $customer = Customer::where('phone', $request->phone)->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone number.',
                ], 404);
            }

            // Guard: OTP must be valid and not expired
            if (!$customer->isOtpValid($request->otp)) {
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
                    'id'                => $customer->id,
                    'name'              => $customer->name,
                    'phone'             => $customer->phone,
                    'email'             => $customer->email,
                    'profile_pic_url'   => $customer->profile_pic
                        ? asset('customer_pics/' . $customer->profile_pic)
                        : null,
                    'status'            => $customer->status,
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

    // -----------------------------------------------------------------------
    // Resend OTP — if not received or expired
    // -----------------------------------------------------------------------

    /**
     * POST /api/customer/resend-otp
     *
     * Accepts: phone
     * Rate-limited: 1 OTP per minute to prevent WhatsApp spam.
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
            $customer = Customer::where('phone', $request->phone)->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with this phone number.',
                ], 404);
            }

            // Rate-limit: allow resend only after 1 minute from last OTP
            if ($customer->otp_expires_at && $customer->otp_expires_at->isFuture()) {
                $secondsLeft = now()->diffInSeconds($customer->otp_expires_at->subMinutes(9), false);
                if ($secondsLeft > 0) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Please wait {$secondsLeft} seconds before requesting a new OTP.",
                    ], 429);
                }
            }

            $otp  = $customer->generateOtp();
            $sent = $this->sendOtpWhatsApp($customer->phone, $otp, $customer->name);

            return response()->json([
                'status'    => true,
                'message'   => 'OTP resent to your WhatsApp number.',
                'phone'     => $customer->phone,
                'otp_debug' => config('app.debug') ? $otp : null,
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
            $whatsappNumber = '91' . $phone; // India country code

            $message = "Hello {$name}! 👋\n\n"
                . "Your *Route-Mate* login OTP is:\n\n"
                . "🔐 *{$otp}*\n\n"
                . "This OTP is valid for *10 minutes*. Do not share it with anyone.\n\n"
                . "_Route-Mate Team_";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.whatsapp.url'), [
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
            $whatsappNumber = '91' . $phone; // India country code

            $message = "Welcome to *Route-Mate*, {$name}! 🎉\n\n"
                . "Your account has been registered successfully.\n\n"
                . "📱 *Phone:* {$phone}\n"
                . "🔑 *Password:* `{$password}`\n\n"
                . "Please use these credentials to login.\n\n"
                . "_Route-Mate Team_";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.whatsapp.url'), [
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
    public function profile(Request $request, $id = null)
    {
        $authCustomer = $request->get('authenticated_customer');
        $customerId   = $id ?? $request->input('customer_id') ?? $request->input('id') ?? $authCustomer?->id;

        if (!$customerId) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer ID or Authorization token is required.',
            ], 422);
        }

        try {
            $customer = $authCustomer && $authCustomer->id == $customerId ? $authCustomer : Customer::find($customerId);

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Customer not found.',
                ], 404);
            }

            $customerData = $customer->toArray();
            unset($customerData['otp_expires_at'], $customerData['phone_verified_at'], $customerData['email_verified_at']);

            $customerData['profile_pic_url'] = $customer->profile_pic
                ? asset('customer_pics/' . $customer->profile_pic)
                : null;

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
     * Update Customer Profile API (name, email, phone, profile_pic)
     *
     * POST /api/customer/update-profile
     */
    public function updateProfile(Request $request)
    {
        $authCustomer = $request->get('authenticated_customer');
        $targetId     = $request->input('customer_id') ?? $authCustomer?->id;

        if (!$targetId) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer ID or Authorization token is required.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|nullable|email|max:255|unique:users,email,' . $targetId,
            'phone'       => 'sometimes|digits:10|unique:users,phone,' . $targetId,
            'profile_pic' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $customer = $authCustomer && $authCustomer->id == $targetId ? $authCustomer : Customer::findOrFail($targetId);

            // Update text fields if provided
            foreach (['name', 'email', 'phone'] as $field) {
                if ($request->has($field)) {
                    $customer->$field = $request->$field;
                }
            }

            // Handle profile pic upload
            $allFiles = $request->allFiles();
            $picFile  = $request->file('profile_pic')
                ?? collect($allFiles)->first(fn($v, $k) => str_starts_with($k, 'profile_pic'));

            if ($picFile) {
                if (!file_exists(public_path('customer_pics'))) {
                    mkdir(public_path('customer_pics'), 0755, true);
                }
                // Delete old profile pic
                if ($customer->profile_pic && file_exists(public_path('customer_pics/' . $customer->profile_pic))) {
                    @unlink(public_path('customer_pics/' . $customer->profile_pic));
                }
                $filename = 'customer_pic_' . time() . '_' . uniqid() . '.' . $picFile->getClientOriginalExtension();
                $picFile->move(public_path('customer_pics'), $filename);
                $customer->profile_pic = $filename;
            }

            $customer->save();

            $customerData = $customer->fresh()->toArray();
            unset($customerData['otp_expires_at'], $customerData['phone_verified_at'], $customerData['email_verified_at']);

            $customerData['profile_pic_url'] = $customer->profile_pic
                ? asset('customer_pics/' . $customer->profile_pic)
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
            $customer = $request->get('authenticated_customer');

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
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
     * POST /api/customer/logout
     */
    public function logout(Request $request)
    {
        try {
            return response()->json([
                'status'  => true,
                'message' => 'Customer logged out successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to logout.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}



