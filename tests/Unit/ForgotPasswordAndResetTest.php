<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordAndResetTest extends TestCase
{
    public function test_forgot_password_generates_and_sends_otp()
    {
        $phone = '98765' . rand(10000, 99999);
        $customer = Customer::create([
            'name' => 'Forgot Password Test User',
            'email' => 'forgot' . time() . rand(100, 999) . '@example.com',
            'phone' => $phone,
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $customer->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Password reset OTP has been sent to your registered email address.',
        ]);

        $customer->refresh();
        $this->assertNotEmpty($customer->otp);
        $this->assertNotNull($customer->otp_expires_at);

        // Clean up
        $customer->delete();
    }

    public function test_reset_password_validates_otp_and_updates_password()
    {
        $phone = '98765' . rand(10000, 99999);
        $customer = Customer::create([
            'name' => 'Reset Password Test User',
            'email' => 'reset' . time() . rand(100, 999) . '@example.com',
            'phone' => $phone,
            'password' => Hash::make('oldpassword123'),
        ]);

        $otp = $customer->generateOtp();

        // 1. Invalid OTP fails
        $failResponse = $this->postJson('/api/auth/reset-password', [
            'email' => $customer->email,
            'otp' => '000000',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $failResponse->assertStatus(422);

        // 2. Valid OTP resets password
        $successResponse = $this->postJson('/api/auth/reset-password', [
            'email' => $customer->email,
            'otp' => $otp,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson([
            'status' => true,
        ]);

        // Customer password in DB is updated and verifies with new password
        $customer->refresh();
        $this->assertTrue(Hash::check('newpassword123', $customer->password));
        $this->assertFalse(Hash::check('oldpassword123', $customer->password));

        // OTP is cleared
        $this->assertNull($customer->otp);

        // Clean up
        $customer->delete();
    }
}
