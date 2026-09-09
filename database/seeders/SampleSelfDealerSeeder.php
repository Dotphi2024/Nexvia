<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\SelfDealerWallet;
use App\Models\CustomerCategoryProgress;
use App\Models\Referral;
use App\Models\WalletTransaction;
use App\Models\FraudFlag;
use App\Models\ReferralStageConfig;
use Illuminate\Support\Str;

class SampleSelfDealerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure categories have referral codes and are referral eligible
        $categoryCodes = [
            1 => 'EV',
            2 => 'TV',
            3 => 'REF',
            4 => 'AC',
            5 => 'WM',
            6 => 'KA',
            7 => 'HA',
        ];

        foreach ($categoryCodes as $catId => $code) {
            Category::where('id', $catId)->update([
                'referral_category_code' => $code,
                'referral_eligible'      => true,
            ]);
        }

        // 2. Ensure products have eligible_referral_value, referral_eligible, self_dealer_eligible
        Product::whereNull('eligible_referral_value')->orWhere('eligible_referral_value', 0)->each(function ($prod) {
            $prod->update([
                'eligible_referral_value' => $prod->mrp,
                'referral_eligible'       => true,
                'self_dealer_eligible'    => true,
            ]);
        });

        // 3. Make sure ReferralStageConfig has entries
        if (ReferralStageConfig::count() === 0) {
            $this->call(ReferralStageConfigSeeder::class);
        }

        // 4. Create or update Self Dealer Customer (Vikram Malhotra)
        $selfDealer = Customer::updateOrCreate(
            ['phone' => '9820011223'],
            [
                'name'                     => 'Vikram Malhotra',
                'email'                    => 'vikram.dealer@nexvia.com',
                'city'                     => 'Mumbai',
                'state'                    => 'Maharashtra',
                'pincode'                  => '400050',
                'is_self_dealer'           => true,
                'self_dealer_status'       => 'active',
                'self_dealer_code'         => 'SD-NEX-0001',
                'referral_code'            => 'VIKRAM20',
                'self_dealer_activated_at' => now()->subDays(10),
                'status'                   => 'active',
            ]
        );

        // 5. Create or update Referee Customer (Ananya Roy)
        $referee = Customer::updateOrCreate(
            ['phone' => '9820033445'],
            [
                'name'           => 'Ananya Roy',
                'email'          => 'ananya.roy@nexvia.com',
                'city'           => 'Pune',
                'state'          => 'Maharashtra',
                'pincode'        => '411001',
                'referred_by_id' => $selfDealer->id,
                'referral_code'  => 'ANANYA10',
                'status'         => 'active',
            ]
        );

        // Get TV Category and Product
        $tvCategory = Category::where('referral_category_code', 'TV')->first() ?? Category::find(2) ?? Category::first();
        $tvProduct  = Product::where('category_id', $tvCategory->id)->first() ?? Product::first();

        $productPrice = $tvProduct ? (float) $tvProduct->mrp : 50000.00;
        $booking20    = $productPrice * 0.20;
        $balance80    = $productPrice * 0.80;

        // 6. Booking 1: Vikram's own purchase (activated his Self Dealer status)
        $dealerBooking = Booking::updateOrCreate(
            ['booking_number' => 'NEX-SD-1001'],
            [
                'user_id'                => $selfDealer->id,
                'product_id'             => $tvProduct->id,
                'product_name'           => $tvProduct->name,
                'model_code'             => $tvProduct->model_code ?? 'NEX-TV55',
                'mrp'                    => $productPrice,
                'booking_amount'         => $booking20,
                'balance_amount'         => $balance80,
                'booking_date'           => now()->subDays(10),
                'balance_due_date'       => now()->addDays(50),
                'payment_type'           => 'booking_20',
                'payment_status'         => 'paid',
                'booking_status'         => 'delivered',
                'transfer_status'        => 'original',
                'non_refundable_accepted'=> true,
                'customer_name'          => $selfDealer->name,
                'customer_phone'         => $selfDealer->phone,
                'shipping_address'       => 'Flat 402, Sea View Apartments, Bandra West',
                'city'                   => 'Mumbai',
                'state'                  => 'Maharashtra',
                'pincode'                => '400050',
            ]
        );

        // Link activation booking to Self Dealer
        $selfDealer->update(['activation_booking_id' => $dealerBooking->id]);

        // 7. Booking 2: Referee Ananya's purchase through Vikram's referral code
        $refereeBooking = Booking::updateOrCreate(
            ['booking_number' => 'NEX-REF-2001'],
            [
                'user_id'                => $referee->id,
                'product_id'             => $tvProduct->id,
                'product_name'           => $tvProduct->name,
                'model_code'             => $tvProduct->model_code ?? 'NEX-TV55',
                'mrp'                    => $productPrice,
                'booking_amount'         => $booking20,
                'balance_amount'         => $balance80,
                'booking_date'           => now()->subDays(3),
                'balance_due_date'       => now()->addDays(57),
                'payment_type'           => 'booking_20',
                'payment_status'         => 'paid',
                'booking_status'         => 'delivered',
                'transfer_status'        => 'original',
                'non_refundable_accepted'=> true,
                'customer_name'          => $referee->name,
                'customer_phone'         => $referee->phone,
                'shipping_address'       => 'Plot 12, Koregaon Park',
                'city'                   => 'Pune',
                'state'                  => 'Maharashtra',
                'pincode'                => '411001',
            ]
        );

        // 8. Referral Entry (Variant A Stage 1: 10% on TV)
        $stage1Rate = 10.00;
        $earnedCredit = ($productPrice * $stage1Rate) / 100; // e.g. ₹5,000

        $referral = Referral::updateOrCreate(
            ['booking_id' => $refereeBooking->id],
            [
                'referrer_id'            => $selfDealer->id,
                'referee_id'             => $referee->id,
                'category_id'            => $tvCategory->id,
                'sequence_in_category'   => 1,
                'referral_stage'         => 1,
                'cycle_number'           => 1,
                'rule_version'           => 'v1.0',
                'benefit_percentage'     => $stage1Rate,
                'product_value'          => $productPrice,
                'eligible_product_value' => $productPrice,
                'credit_earned'          => $earnedCredit,
                'status'                 => 'qualified',
                'transaction_type'       => 'referral_incentive',
                'notes'                  => 'Stage 1 Referral qualified upon delivery. 10% awarded to Self Dealer wallet.',
                'approved_at'            => now()->subDays(1),
            ]
        );

        // 9. CustomerCategoryProgress Entry
        // Since Vikram completed Stage 1, his current stage advances to Stage 2 (12%)
        CustomerCategoryProgress::updateOrCreate(
            [
                'user_id'     => $selfDealer->id,
                'category_id' => $tvCategory->id,
            ],
            [
                'referral_count'           => 1,
                'current_stage'            => 2,
                'cycle_number'             => 1,
                'current_tier_percentage'  => 12.00,
                'total_referrals_all_time' => 1,
            ]
        );

        // 10. Self Dealer Wallet
        // Activation credit (20% of own purchase = ₹10,000) + Stage 1 credit (₹5,000) = ₹15,000
        $activationPoints = ($productPrice * 20.00) / 100;
        $totalEarned = $activationPoints + $earnedCredit;

        SelfDealerWallet::updateOrCreate(
            ['user_id' => $selfDealer->id],
            [
                'total_earned'     => $totalEarned,
                'available_points' => $totalEarned,
                'pending_points'   => 0.00,
                'redeemed_points'  => 0.00,
                'reversed_points'  => 0.00,
            ]
        );

        // 11. Wallet Transactions (Ledger)
        // Txn A: Activation Credit (20%)
        WalletTransaction::updateOrCreate(
            [
                'user_id'          => $selfDealer->id,
                'transaction_type' => 'self_dealer_activation',
            ],
            [
                'amount'               => $activationPoints,
                'type'                 => 'credit',
                'source'               => 'self_dealer_activation',
                'status'               => 'available',
                'booking_id'           => $dealerBooking->id,
                'description'          => 'Self Dealer Activation Points (20% of own purchase ₹' . number_format($productPrice, 2) . ')',
                'incentive_percentage' => 20.00,
                'rule_version'         => 'v1.0',
                'available_at'         => now()->subDays(10),
            ]
        );

        // Txn B: Stage 1 Referral Incentive (10%)
        WalletTransaction::updateOrCreate(
            [
                'user_id'          => $selfDealer->id,
                'referral_id'      => $referral->id,
            ],
            [
                'amount'               => $earnedCredit,
                'type'                 => 'credit',
                'source'               => 'referral_commission',
                'transaction_type'     => 'referral_incentive',
                'status'               => 'available',
                'booking_id'           => $refereeBooking->id,
                'category_id'          => $tvCategory->id,
                'referral_stage'       => 1,
                'cycle_number'         => 1,
                'incentive_percentage' => 10.00,
                'rule_version'         => 'v1.0',
                'description'          => 'Stage 1 Referral Incentive (10%) for Booking #' . $refereeBooking->booking_number,
                'available_at'         => now()->subDays(1),
            ]
        );

        // 12. Fraud Flag Entry (for admin review demo)
        FraudFlag::updateOrCreate(
            [
                'user_id'     => $referee->id,
                'referrer_id' => $selfDealer->id,
                'booking_id'  => $refereeBooking->id,
            ],
            [
                'flag_type'   => 'matching_phone_pattern',
                'flag_reason' => 'Audit check: Referee delivery pincode and area match previously seen cluster. Flagged for verification.',
                'status'      => 'pending_review',
            ]
        );

        $this->command->info('✅ Sample data seeded successfully in all Self Dealer tables!');
    }
}
