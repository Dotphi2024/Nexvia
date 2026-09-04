<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Referral;
use App\Models\CustomerCategoryProgress;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class ReferralCommissionService
{
    /**
     * Process referral reward when a referee completes a product booking.
     */
    public function processBookingReferral(Booking $booking): ?Referral
    {
        $referee = Customer::find($booking->user_id);
        if (!$referee || !$referee->referred_by_id) {
            return null;
        }

        $referrer = Customer::find($referee->referred_by_id);
        if (!$referrer) {
            return null;
        }

        $product = $booking->product;
        $categoryId = $product ? $product->category_id : null;
        if (!$categoryId) {
            return null;
        }

        return DB::transaction(function () use ($referrer, $referee, $booking, $categoryId) {
            // Get or initialize category progress for referrer
            $progress = CustomerCategoryProgress::firstOrCreate(
                ['user_id' => $referrer->id, 'category_id' => $categoryId],
                ['referral_count' => 0, 'current_tier_percentage' => 10.00]
            );

            // Increment referral sequence
            $sequence = $progress->referral_count + 1;

            // Determine commission percentage: 1st=10%, 2nd=12%, 3rd=15%, 4th=18%, 5th+=20%
            $tierMap = [
                1 => 10.00,
                2 => 12.00,
                3 => 15.00,
                4 => 18.00,
            ];
            $benefitPercentage = $tierMap[$sequence] ?? 20.00;

            // Calculate credit earned based on Product MRP
            $productValue = $booking->mrp;
            $creditEarned = $productValue * ($benefitPercentage / 100);

            // Update category progress
            $progress->referral_count = $sequence;
            $nextSequence = $sequence + 1;
            $progress->current_tier_percentage = $tierMap[$nextSequence] ?? 20.00;
            $progress->save();

            // Record Referral
            $referral = Referral::create([
                'referrer_id'          => $referrer->id,
                'referee_id'           => $referee->id,
                'booking_id'           => $booking->id,
                'category_id'          => $categoryId,
                'sequence_in_category' => $sequence,
                'benefit_percentage'   => $benefitPercentage,
                'product_value'        => $productValue,
                'credit_earned'        => $creditEarned,
                'status'               => 'credited',
            ]);

            // Add Product Credit to Referrer's Wallet
            $referrer->wallet_balance = ($referrer->wallet_balance ?? 0) + $creditEarned;
            $referrer->save();

            // Record Wallet Transaction
            WalletTransaction::create([
                'user_id'     => $referrer->id,
                'amount'      => $creditEarned,
                'type'        => 'credit',
                'source'      => 'referral_commission',
                'booking_id'  => $booking->id,
                'description' => "Earned {$benefitPercentage}% NEXVIA Product Credit (₹" . number_format($creditEarned, 2) . ") for referral of {$booking->product_name}",
            ]);

            return $referral;
        });
    }
}
