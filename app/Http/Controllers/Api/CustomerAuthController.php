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
        $name = trim($request->input('fullName') ?? $request->input('name') ?? '');
        if ($name !== '') {
            $request->merge(['name' => $name, 'fullName' => $name]);
        }

        $validator = Validator::make($request->all(), [
            'fullName'    => 'nullable|string|max:255',
            'name'        => 'required|string|max:255',
            'phone'       => 'required|digits:10|unique:users,phone',
            'email'       => 'nullable|email|max:255|unique:users,email',
            'password'    => 'nullable|string',
            'fcm_token'   => 'nullable|string',
        ], [
            'name.required'  => 'Full name (fullName) is required.',
            'phone.required' => 'Phone number is required.',
            'phone.digits'   => 'Phone number must be exactly 10 digits.',
            'phone.unique'   => 'This phone number is already registered.',
            'email.unique'   => 'This email is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $password = $request->input('password') ?: trim($request->phone);

            $customerData = [
                'name'              => $name,
                'phone'             => trim($request->phone),
                'email'             => $request->email ? strtolower(trim($request->email)) : null,
                'password'          => $password, // auto-hashed via model cast
                'fcm_token'         => $request->fcm_token ?? null,
                'status'            => 'active',
            ];

            $customer = Customer::create($customerData);
            $token = $customer->generateApiToken();

            // Send Welcome notification on WhatsApp if configured
            $this->sendWelcomeWhatsApp($customer->phone, $password, $customer->name);

            return response()->json([
                'status'  => true,
                'message' => 'Registration successful.',
                'token'   => $token,
                'data'    => [
                    'id'        => $customer->id,
                    'fullName'  => $customer->name,
                    'name'      => $customer->name,
                    'phone'     => $customer->phone,
                    'email'     => $customer->email,
                    'fcm_token' => $customer->fcm_token,
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
     * Accepts: emailOrPhone (or phone or email), password
     */
    public function login(Request $request)
    {
        $loginInput = trim(
            $request->input('emailOrPhone') ??
            $request->input('phone') ??
            $request->input('email') ??
            $request->input('login') ?? ''
        );

        $validator = Validator::make($request->all(), [
            'password'  => 'required|string',
            'fcm_token' => 'nullable|string',
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

            // Save FCM token if passed during login
            if ($request->has('fcm_token')) {
                $customer->fcm_token = $request->fcm_token;
                $customer->save();
            }

            // Credentials are valid → generate token and return customer data directly
            $token = $customer->generateApiToken();

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'token'   => $token,
                'data'    => [
                    'id'              => $customer->id,
                    'fullName'        => $customer->name,
                    'name'            => $customer->name,
                    'phone'           => $customer->phone,
                    'email'           => $customer->email,
                    'avatarUrl'       => $customer->profile_pic
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

    /**
     * POST /api/auth/send-otp
     * Accepts: phone
     */
    public function sendOtp(Request $request)
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

            $otp  = $customer->generateOtp();
            $this->sendOtpWhatsApp($customer->phone, $otp, $customer->name);

            return response()->json([
                'status'    => true,
                'message'   => 'OTP sent successfully.',
                'phone'     => $customer->phone,
                'otp'       => config('app.debug') ? $otp : null,
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
     * Accepts: phone, otpCode (or otp)
     */
    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otpCode') ?? $request->input('otp');
        $request->merge(['otp' => $otp]);

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
                    'fullName'          => $customer->name,
                    'name'              => $customer->name,
                    'phone'             => $customer->phone,
                    'email'             => $customer->email,
                    'avatarUrl'         => $customer->profile_pic
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
     * Accepts: email (or phone)
     */
    public function forgotPassword(Request $request)
    {
        $input = trim($request->input('email') ?? $request->input('phone') ?? '');

        if (empty($input)) {
            return response()->json([
                'status'  => false,
                'message' => 'Email or phone number is required.',
            ], 422);
        }

        try {
            $customer = Customer::where('email', strtolower($input))
                ->orWhere('phone', $input)
                ->first();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No account found with the provided email or phone.',
                ], 404);
            }

            $otp = $customer->generateOtp();
            $this->sendOtpWhatsApp($customer->phone, $otp, $customer->name);

            return response()->json([
                'status'    => true,
                'message'   => 'Password reset OTP sent to your registered WhatsApp phone number.',
                'phone'     => $customer->phone,
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
            $customer = Customer::where('phone', $request->phone)->first();

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
        $authCustomer = $request->get('authenticated_customer') ?? $request->user();
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
            unset($customerData['otp_expires_at'], $customerData['phone_verified_at'], $customerData['email_verified_at'], $customerData['profile_pic']);

            $customerData['fullName']  = $customer->name;
            $customerData['avatarUrl'] = $customer->profile_pic
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
     * PUT /api/user/profile or POST /api/customer/update-profile
     * Accepts: fullName (or name), email, phone, avatarUrl (or profile_pic file/URL)
     */
    public function updateProfile(Request $request)
    {
        $authCustomer = $request->get('authenticated_customer') ?? $request->user();
        $targetId     = $request->input('id') ?? $request->input('user_id') ?? $request->input('customer_id') ?? $authCustomer?->id;

        if (!$targetId) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer ID or Authorization token is required.',
            ], 422);
        }

        $fullName = $request->input('fullName') ?? $request->input('name');
        if ($fullName) {
            $request->merge(['name' => trim($fullName)]);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|nullable|email|max:255|unique:users,email,' . $targetId,
            'phone'       => 'sometimes|digits:10|unique:users,phone,' . $targetId,
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
            $customer = $authCustomer && $authCustomer->id == $targetId ? $authCustomer : Customer::findOrFail($targetId);

            // Update text fields if provided
            foreach (['name', 'email', 'phone'] as $field) {
                if ($request->has($field) && !empty($request->$field)) {
                    $customer->$field = $request->$field;
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
            $token = $request->input('token') ?? $request->input('refreshToken') ?? $request->bearerToken();
            $phone = $request->input('phone');

            if ($token) {
                Customer::where('api_token', $token)->update(['api_token' => null]);
            } elseif ($phone) {
                Customer::where('phone', $phone)->update(['api_token' => null]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'User logged out successfully.',
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



