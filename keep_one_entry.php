<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// 1. Categories -> Exactly 1
DB::table('categories')->truncate();
DB::table('categories')->insert([
    'id'                     => 1,
    'name'                   => 'Smart LED TV',
    'slug'                   => 'smart-led-tv',
    'type'                   => 'electronics',
    'referral_category_code' => 'TV',
    'referral_eligible'      => 1,
    'commission_percentage'  => 10.00,
    'image'                  => null,
    'description'            => 'High definition Smart LED televisions with referral benefits',
    'is_active'              => 1,
    'sort_order'             => 1,
    'created_at'             => now(),
    'updated_at'             => now(),
]);

// 2. Products -> Exactly 1
DB::table('products')->truncate();
DB::table('products')->insert([
    'id'                      => 1,
    'category_id'             => 1,
    'name'                    => 'NEXVIA 55-inch Ultra HD 4K Smart LED TV',
    'model_code'              => 'NEX-TV55-4K',
    'sku'                     => 'SKU-TV-001',
    'slug'                    => 'nexvia-55-inch-ultra-hd-4k-smart-led-tv',
    'mrp'                     => 50000.00,
    'booking_percentage'      => 20,
    'booking_amount'          => 10000.00,
    'balance_amount'          => 40000.00,
    'eligible_referral_value' => 50000.00,
    'referral_eligible'       => 1,
    'self_dealer_eligible'    => 1,
    'stock'                   => 25,
    'video_url'               => null,
    'offer_text'              => 'Self Dealer Special — 20% Initial Booking',
    'main_image'              => null,
    'gallery'                 => null,
    'warranty_info'           => '3 Years Comprehensive Warranty',
    'installation_info'       => 'Free Standard Wall Mount Installation',
    'delivery_info'           => 'Delivered in 3-5 business days',
    'is_featured'             => 1,
    'status'                  => 'active',
    'created_at'              => now(),
    'updated_at'              => now(),
]);

// 3. Users -> Exactly 1
DB::table('users')->truncate();
DB::table('users')->insert([
    'id'                       => 1,
    'name'                     => 'Vikram Malhotra',
    'email'                    => 'vikram.dealer@nexvia.com',
    'phone'                    => '9820011223',
    'referral_code'            => 'VIKRAM20',
    'referred_by_id'           => null,
    'pincode'                  => '400050',
    'city'                     => 'Mumbai',
    'state'                    => 'Maharashtra',
    'dob'                      => '1990-05-15',
    'gst_number'               => '27AABCU9603R1ZM',
    'wallet_balance'           => 15000.00,
    'is_self_dealer'           => 1,
    'self_dealer_code'         => 'SD-NEX-0001',
    'self_dealer_status'       => 'active',
    'self_dealer_activated_at' => now()->subDays(10),
    'activation_booking_id'    => 1,
    'status'                   => 'active',
    'created_at'               => now()->subDays(10),
    'updated_at'               => now(),
]);

// 4. Bookings -> Exactly 1
DB::table('bookings')->truncate();
DB::table('bookings')->insert([
    'id'                      => 1,
    'booking_number'          => 'NEX-SD-1001',
    'user_id'                 => 1,
    'product_id'              => 1,
    'product_name'            => 'NEXVIA 55-inch Ultra HD 4K Smart LED TV',
    'model_code'              => 'NEX-TV55-4K',
    'mrp'                     => 50000.00,
    'booking_amount'          => 10000.00,
    'balance_amount'          => 40000.00,
    'booking_date'            => now()->subDays(10),
    'balance_due_date'        => now()->addDays(50),
    'payment_type'            => 'booking_20',
    'payment_status'          => 'paid',
    'booking_status'          => 'delivered',
    'transfer_status'         => 'original',
    'non_refundable_accepted' => 1,
    'customer_name'           => 'Vikram Malhotra',
    'customer_phone'          => '9820011223',
    'shipping_address'        => 'Flat 402, Sea View Apartments, Bandra West',
    'pincode'                 => '400050',
    'city'                    => 'Mumbai',
    'state'                   => 'Maharashtra',
    'created_at'              => now()->subDays(10),
    'updated_at'              => now(),
]);

// 5. Self Dealer Wallets -> Exactly 1
DB::table('self_dealer_wallets')->truncate();
DB::table('self_dealer_wallets')->insert([
    'id'               => 1,
    'user_id'          => 1,
    'total_earned'     => 15000.00,
    'available_points' => 15000.00,
    'pending_points'   => 0.00,
    'redeemed_points'  => 0.00,
    'reversed_points'  => 0.00,
    'created_at'       => now()->subDays(10),
    'updated_at'       => now(),
]);

// 6. Customer Category Progress -> Exactly 1
DB::table('customer_category_progress')->truncate();
DB::table('customer_category_progress')->insert([
    'id'                       => 1,
    'user_id'                  => 1,
    'category_id'              => 1,
    'referral_count'           => 1,
    'current_stage'            => 2,
    'cycle_number'             => 1,
    'total_referrals_all_time' => 1,
    'current_tier_percentage'  => 12.00,
    'created_at'               => now()->subDays(10),
    'updated_at'               => now(),
]);

// 7. Referrals -> Exactly 1
DB::table('referrals')->truncate();
DB::table('referrals')->insert([
    'id'                     => 1,
    'referrer_id'            => 1,
    'referee_id'             => 1,
    'booking_id'             => 1,
    'category_id'            => 1,
    'sequence_in_category'   => 1,
    'benefit_percentage'     => 10.00,
    'product_value'          => 50000.00,
    'credit_earned'          => 5000.00,
    'status'                 => 'qualified',
    'transaction_type'       => 'referral_incentive',
    'eligible_product_value' => 50000.00,
    'cycle_number'           => 1,
    'referral_stage'         => 1,
    'rule_version'           => 'v1.0',
    'notes'                  => 'Stage 1 Referral (10%) qualified on purchase',
    'approved_at'            => now()->subDays(1),
    'created_at'             => now()->subDays(2),
    'updated_at'             => now(),
]);

// 8. Wallet Transactions -> Exactly 1
DB::table('wallet_transactions')->truncate();
DB::table('wallet_transactions')->insert([
    'id'                   => 1,
    'user_id'              => 1,
    'amount'               => 5000.00,
    'type'                 => 'credit',
    'source'               => 'referral_commission',
    'booking_id'           => 1,
    'description'          => 'Stage 1 Referral Incentive (10%) for Booking #NEX-SD-1001',
    'transaction_type'     => 'referral_incentive',
    'status'               => 'available',
    'referral_id'          => 1,
    'category_id'          => 1,
    'incentive_percentage' => 10.00,
    'referral_stage'       => 1,
    'cycle_number'         => 1,
    'rule_version'         => 'v1.0',
    'available_at'         => now()->subDays(1),
    'created_at'           => now()->subDays(1),
    'updated_at'           => now(),
]);

// 9. Fraud Flags -> Exactly 1
DB::table('fraud_flags')->truncate();
DB::table('fraud_flags')->insert([
    'id'          => 1,
    'user_id'     => 1,
    'referrer_id' => 1,
    'booking_id'  => 1,
    'flag_type'   => 'self_referral_attempt',
    'flag_reason' => 'Audit review: Referral attempted using own registered account credentials',
    'status'      => 'pending_review',
    'admin_notes' => null,
    'reviewed_by' => null,
    'reviewed_at' => null,
    'created_at'  => now()->subHours(5),
    'updated_at'  => now()->subHours(5),
]);

// 10. Referral Stage Configs -> Exactly 1
DB::table('referral_stage_configs')->truncate();
DB::table('referral_stage_configs')->insert([
    'id'                           => 1,
    'stage_number'                 => 1,
    'incentive_percentage'         => 10.00,
    'activation_credit_percentage' => 20.00,
    'is_active'                    => 1,
    'version'                      => 'v1.0',
    'notes'                        => 'Variant A — Stage 1 (10% rate, 20% activation)',
    'created_at'                   => now(),
    'updated_at'                   => now(),
]);

// 11. Booking Transfers -> Exactly 1
DB::table('booking_transfers')->truncate();
DB::table('booking_transfers')->insert([
    'id'                      => 1,
    'booking_id'              => 1,
    'from_user_id'            => 1,
    'to_name'                 => 'Rahul Verma',
    'to_phone'                => '9876500001',
    'to_user_id'              => null,
    'transfer_otp'            => '123456',
    'transfer_otp_expires_at' => now()->addHours(24),
    'status'                  => 'pending',
    'created_at'              => now(),
    'updated_at'              => now(),
]);

// 12. Service Requests -> Exactly 1
DB::table('service_requests')->truncate();
DB::table('service_requests')->insert([
    'id'            => 1,
    'ticket_number' => 'SR-2026-0001',
    'user_id'       => 1,
    'booking_id'    => 1,
    'subject'       => 'Wall Mount Installation Request',
    'service_type'  => 'installation',
    'status'        => 'open',
    'details'       => 'Customer requested complimentary TV wall mount installation service.',
    'created_at'    => now(),
    'updated_at'    => now(),
]);

DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "✅ All tables reset to EXACTLY ONE entry each!" . PHP_EOL;
