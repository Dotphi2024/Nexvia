<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\SelfDealerWallet;
use App\Models\Referral;
use App\Services\ReferralCommissionService;

class CreditBookingAndActivationStakeTest extends TestCase
{
    public function test_self_dealer_retains_status_when_redeeming_within_referral_credits()
    {
        $service = app(ReferralCommissionService::class);
        $firstBooking = \App\Models\Booking::first();
        $bookingId = $firstBooking ? $firstBooking->id : null;

        $customer = Customer::create([
            'name' => 'Retain Stake User',
            'email' => 'retain' . time() . rand(100, 999) . '@example.com',
            'phone' => '98888' . rand(10000, 99999),
            'password' => bcrypt('password123'),
            'is_self_dealer' => true,
            'self_dealer_status' => 'active',
            'wallet_balance' => 3500.00,
        ]);

        $referral = Referral::create([
            'referrer_id' => $customer->id,
            'referee_id' => $customer->id,
            'category_id' => 1,
            'booking_id' => $bookingId,
            'referee_name' => 'Self',
            'referee_phone' => '9888888888',
            'booking_number' => 'NEX-ACT-888',
            'category_name' => 'Test Product',
            'stage' => 1,
            'percentage' => 20,
            'product_value' => 10000,
            'transaction_type' => 'activation',
            'credit_earned' => 2000.00,
            'credited_points' => 2000.00,
            'pending_points' => 0,
            'status' => 'available',
        ]);

        $wallet = SelfDealerWallet::create([
            'user_id' => $customer->id,
            'available_points' => 3500.00,
            'pending_points' => 0.00,
            'redeemed_points' => 1500.00,
            'total_earned' => 5000.00,
        ]);

        $result = $service->checkAndEnforceActivationStake($customer, 1500.00);

        // Required stake = 2000. Balance = 3500 >= 2000. Self-Dealer is NOT cancelled!
        $this->assertFalse($result['cancelled']);
        $customer->refresh();
        $this->assertTrue((bool)$customer->is_self_dealer);
        $this->assertEquals('active', $customer->self_dealer_status);

        // Clean up
        $referral->delete();
        $customer->delete();
        $wallet->delete();
    }

    public function test_self_dealer_account_cancelled_when_activation_credit_is_consumed()
    {
        $service = app(ReferralCommissionService::class);
        $firstBooking = \App\Models\Booking::first();
        $bookingId = $firstBooking ? $firstBooking->id : null;

        // Required stake = 2000. Customer redeems until balance is only 500 (< 2000).
        $customer = Customer::create([
            'name' => 'Stake Test User',
            'email' => 'staketest' . time() . rand(100, 999) . '@example.com',
            'phone' => '98999' . rand(10000, 99999),
            'password' => bcrypt('password123'),
            'is_self_dealer' => true,
            'self_dealer_status' => 'active',
            'wallet_balance' => 500.00,
        ]);

        $referral = Referral::create([
            'referrer_id' => $customer->id,
            'referee_id' => $customer->id,
            'category_id' => 1,
            'booking_id' => $bookingId,
            'referee_name' => 'Self',
            'referee_phone' => '9899999999',
            'booking_number' => 'NEX-ACT-999',
            'category_name' => 'Test Product',
            'stage' => 1,
            'percentage' => 20,
            'product_value' => 10000,
            'transaction_type' => 'activation',
            'credit_earned' => 2000.00,
            'credited_points' => 2000.00,
            'pending_points' => 0,
            'status' => 'available',
        ]);

        $wallet = SelfDealerWallet::create([
            'user_id' => $customer->id,
            'available_points' => 500.00,
            'pending_points' => 0.00,
            'redeemed_points' => 2500.00,
            'total_earned' => 3000.00,
        ]);

        $result = $service->checkAndEnforceActivationStake($customer, 2500.00);

        // Verify Self-Dealer is cancelled
        $this->assertTrue($result['cancelled']);
        $customer->refresh();
        $this->assertFalse((bool)$customer->is_self_dealer);
        $this->assertEquals('cancelled', $customer->self_dealer_status);

        // Clean up
        $referral->delete();
        $customer->delete();
        $wallet->delete();
    }
}
