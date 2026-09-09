<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCategoryProgress;
use App\Models\FraudFlag;
use App\Models\Referral;
use App\Models\ReferralStageConfig;
use App\Models\SelfDealerWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralCommissionService
{
    // =========================================================================
    // STEP 1 — SELF DEALER ACTIVATION
    // Triggered when a customer completes their first eligible own purchase.
    // Awards 20% Activation Points (PENDING until qualifying conditions met).
    // =========================================================================

    public function activateSelfDealer(Customer $customer, Booking $booking): bool
    {
        if ($customer->is_self_dealer) {
            return false; // Already a self dealer
        }

        $product = $booking->product;
        if (!$product || !$product->self_dealer_eligible) {
            return false; // Product not eligible for self dealer program
        }

        return DB::transaction(function () use ($customer, $booking, $product) {
            // 1. Generate unique Self Dealer Code and Referral Code
            $selfDealerCode = $this->generateSelfDealerCode();
            $referralCode   = $this->generateReferralCode($customer);

            // 2. Activate Self Dealer Status
            $customer->is_self_dealer           = true;
            $customer->self_dealer_code         = $selfDealerCode;
            $customer->referral_code            = $referralCode;
            $customer->self_dealer_status       = 'active';
            $customer->self_dealer_activated_at = now();
            $customer->activation_booking_id    = $booking->id;
            $customer->save();

            // 3. Calculate Activation Points (20% of eligible product value)
            $activationRate   = ReferralStageConfig::getActivationRate(); // from DB, default 20%
            $eligibleValue    = $product->eligible_referral_value ?? $product->mrp;
            $activationPoints = $eligibleValue * ($activationRate / 100);

            // 4. Create Wallet
            $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $customer->id]);
            $wallet->creditPending($activationPoints);

            // 5. Create Referral record for Activation Points (status = pending)
            Referral::create([
                'referrer_id'            => $customer->id,
                'referee_id'             => $customer->id, // own purchase
                'booking_id'             => $booking->id,
                'category_id'            => $product->category_id,
                'sequence_in_category'   => 0,
                'benefit_percentage'     => $activationRate,
                'product_value'          => $product->mrp,
                'eligible_product_value' => $eligibleValue,
                'credit_earned'          => $activationPoints,
                'status'                 => 'pending',
                'transaction_type'       => 'activation',
                'cycle_number'           => 1,
                'referral_stage'         => null,
                'rule_version'           => 'v1.0',
                'notes'                  => "Self Dealer Activation — {$activationRate}% of ₹{$eligibleValue}",
            ]);

            // 6. Record Wallet Transaction
            WalletTransaction::create([
                'user_id'              => $customer->id,
                'amount'               => $activationPoints,
                'type'                 => 'credit',
                'source'               => 'self_dealer_activation',
                'booking_id'           => $booking->id,
                'description'          => "Self Dealer Activation Points — {$activationRate}% of ₹" . number_format($eligibleValue, 2),
                'transaction_type'     => 'activation_points',
                'status'               => 'pending',
                'category_id'          => $product->category_id,
                'incentive_percentage' => $activationRate,
                'rule_version'         => 'v1.0',
            ]);

            return true;
        });
    }

    // =========================================================================
    // STEP 2 — PROCESS REFERRAL BOOKING
    // Triggered when a referred customer places a booking using a referral code.
    // Runs fraud checks, links referral to order, creates PENDING points.
    // =========================================================================

    /**
     * Alias for processReferralBooking
     */
    public function processBookingReferral(Booking $booking): ?Referral
    {
        return $this->processReferralBooking($booking);
    }

    public function processReferralBooking(Booking $booking): ?Referral
    {
        // Get the referred customer
        $referee = Customer::find($booking->user_id);
        if (!$referee || !$referee->referred_by_id) {
            return null;
        }

        // Get the referrer (Self Dealer)
        $referrer = Customer::find($referee->referred_by_id);
        if (!$referrer || !$referrer->is_self_dealer || $referrer->self_dealer_status !== 'active') {
            return null;
        }

        $product = $booking->product;
        if (!$product || !$product->referral_eligible) {
            return null;
        }

        $category = Category::find($product->category_id);
        if (!$category || !$category->referral_eligible) {
            return null;
        }

        // Run fraud checks
        $fraudResult = $this->runFraudChecks($booking, $referrer, $referee);
        if ($fraudResult['flagged']) {
            FraudFlag::create([
                'user_id'     => $referee->id,
                'referrer_id' => $referrer->id,
                'booking_id'  => $booking->id,
                'flag_type'   => $fraudResult['flag_type'],
                'flag_reason' => $fraudResult['reason'],
                'status'      => 'pending_review',
            ]);
            // Points stay PENDING and won't qualify until admin clears the flag
        }

        return DB::transaction(function () use ($referrer, $referee, $booking, $product, $fraudResult) {
            $categoryId = $product->category_id;

            // Get or initialize category progress for referrer (starts at Stage 1 if first time)
            $progress = CustomerCategoryProgress::firstOrCreate(
                ['user_id' => $referrer->id, 'category_id' => $categoryId],
                [
                    'referral_count'          => 0,
                    'current_stage'           => 1,
                    'cycle_number'            => 1,
                    'total_referrals_all_time'=> 0,
                    'current_tier_percentage' => ReferralStageConfig::getRateForStage(1),
                ]
            );

            // Get current stage rate from DB config (NOT hardcoded)
            $currentStage   = $progress->current_stage;
            $stageRate      = ReferralStageConfig::getRateForStage($currentStage);
            $eligibleValue  = $product->eligible_referral_value ?? $product->mrp;
            $pointsEarned   = $eligibleValue * ($stageRate / 100);

            // Create Referral record (PENDING — not yet qualified)
            $referral = Referral::create([
                'referrer_id'            => $referrer->id,
                'referee_id'             => $referee->id,
                'booking_id'             => $booking->id,
                'category_id'            => $categoryId,
                'sequence_in_category'   => $progress->total_referrals_all_time + 1,
                'benefit_percentage'     => $stageRate,
                'product_value'          => $product->mrp,
                'eligible_product_value' => $eligibleValue,
                'credit_earned'          => $pointsEarned,
                'status'                 => 'pending',
                'transaction_type'       => 'referral',
                'cycle_number'           => $progress->cycle_number,
                'referral_stage'         => $currentStage,
                'rule_version'           => 'v1.0',
                'notes'                  => $fraudResult['flagged']
                    ? "FRAUD FLAG: {$fraudResult['reason']}"
                    : "Stage {$currentStage} | Cycle {$progress->cycle_number} | {$stageRate}% of ₹{$eligibleValue}",
            ]);

            // Add PENDING points to wallet
            $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $referrer->id]);
            $wallet->creditPending($pointsEarned);

            // Record Wallet Transaction (PENDING)
            WalletTransaction::create([
                'user_id'              => $referrer->id,
                'amount'               => $pointsEarned,
                'type'                 => 'credit',
                'source'               => 'referral_incentive',
                'booking_id'           => $booking->id,
                'description'          => "Referral Incentive Points — Stage {$currentStage} | {$stageRate}% of ₹" . number_format($eligibleValue, 2),
                'transaction_type'     => 'referral_incentive',
                'status'               => 'pending',
                'referral_id'          => $referral->id,
                'category_id'          => $categoryId,
                'incentive_percentage' => $stageRate,
                'referral_stage'       => $currentStage,
                'cycle_number'         => $progress->cycle_number,
                'rule_version'         => 'v1.0',
            ]);

            return $referral;
        });
    }

    // =========================================================================
    // STEP 3 — QUALIFY REFERRAL
    // Called when an order meets qualifying conditions (delivered/balance paid).
    // Moves points from PENDING → AVAILABLE and advances the stage.
    // AFTER STAGE 5 → RESETS TO STAGE 1 (Variant A cycle).
    // =========================================================================

    public function qualifyReferral(Referral $referral): bool
    {
        if (!$referral->isPending()) {
            return false;
        }

        return DB::transaction(function () use ($referral) {
            $referrer   = Customer::find($referral->referrer_id);
            $categoryId = $referral->category_id;
            $points     = $referral->credit_earned;

            // 1. Move points: PENDING → AVAILABLE
            $referral->status      = 'available';
            $referral->approved_at = now();
            $referral->save();

            // 2. Update wallet
            $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $referrer->id]);
            $wallet->qualifyPoints($points);

            // 3. Update wallet transaction status
            WalletTransaction::where('referral_id', $referral->id)
                ->where('status', 'pending')
                ->update(['status' => 'available', 'available_at' => now()]);

            // 4. Also qualify referrer's own wallet_balance (legacy field)
            $referrer->wallet_balance = ($referrer->wallet_balance ?? 0) + $points;
            $referrer->save();

            // 5. Advance stage ONLY for referral type (not activation)
            if ($referral->transaction_type === 'referral') {
                $progress = CustomerCategoryProgress::where('user_id', $referrer->id)
                    ->where('category_id', $categoryId)
                    ->first();

                if ($progress) {
                    $wasStage5 = ($progress->current_stage === 5);
                    $progress->advanceStage(); // handles reset + cycle increment

                    if ($wasStage5) {
                        // Log cycle completion
                        $referral->notes = ($referral->notes ?? '') . ' | CYCLE COMPLETED — Reset to Stage 1';
                        $referral->save();
                    }
                }
            }

            return true;
        });
    }

    // =========================================================================
    // STEP 4 — REVERSE REFERRAL
    // Called when order is cancelled, returned, or fraud confirmed.
    // Reverses points with full audit trail. Stage does NOT advance.
    // =========================================================================

    public function reverseReferral(Referral $referral, string $reason = 'Order cancelled'): bool
    {
        if ($referral->isReversed()) {
            return false;
        }

        return DB::transaction(function () use ($referral, $reason) {
            $referrer = Customer::find($referral->referrer_id);
            $points   = $referral->credit_earned;
            $wasAvailable = $referral->isAvailable();

            // 1. Mark referral as reversed
            $referral->status      = 'reversed';
            $referral->reversed_at = now();
            $referral->notes       = ($referral->notes ?? '') . " | REVERSED: {$reason}";
            $referral->save();

            // 2. Reverse wallet points
            $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $referrer->id]);
            $wallet->reversePoints($points, $wasAvailable ? 'available' : 'pending');

            // 3. Update wallet transaction
            WalletTransaction::where('referral_id', $referral->id)
                ->whereIn('status', ['pending', 'available'])
                ->update(['status' => 'reversed', 'reversed_at' => now()]);

            // 4. Deduct from legacy wallet_balance if points were available
            if ($wasAvailable) {
                $referrer->wallet_balance = max(0, ($referrer->wallet_balance ?? 0) - $points);
                $referrer->save();
            }

            // NOTE: Stage does NOT reverse — audit trail remains intact.

            return true;
        });
    }

    // =========================================================================
    // FRAUD CHECKS
    // Detects self-referral, duplicate mobile, and other suspicious activity.
    // =========================================================================

    protected function runFraudChecks(Booking $booking, Customer $referrer, Customer $referee): array
    {
        // Check 1: Self-referral (same user ID)
        if ($referrer->id === $referee->id) {
            return ['flagged' => true, 'flag_type' => 'self_referral', 'reason' => 'Referrer and referee are the same user.'];
        }

        // Check 2: Same mobile number
        if ($referrer->phone && $referrer->phone === $referee->phone) {
            return ['flagged' => true, 'flag_type' => 'duplicate_mobile', 'reason' => 'Referrer and referee share the same mobile number.'];
        }

        // Check 3: Referee's email matches referrer
        if ($referrer->email && $referrer->email === $referee->email) {
            return ['flagged' => true, 'flag_type' => 'duplicate_email', 'reason' => 'Referrer and referee share the same email address.'];
        }

        // Check 4: Repeated cancellations by this referee (>3 cancelled referral bookings)
        $cancelledCount = Referral::where('referee_id', $referee->id)
            ->where('status', 'reversed')
            ->count();
        if ($cancelledCount >= 3) {
            return ['flagged' => true, 'flag_type' => 'repeated_cancellation', 'reason' => "Referee has {$cancelledCount} previously reversed referral orders."];
        }

        return ['flagged' => false, 'flag_type' => null, 'reason' => null];
    }

    // =========================================================================
    // HELPER — Generate Unique Self Dealer Code
    // Format: NEX-XXXXXX (e.g. NEX-001234)
    // =========================================================================

    protected function generateSelfDealerCode(): string
    {
        do {
            $number = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $code   = "NEX-{$number}";
        } while (Customer::where('self_dealer_code', $code)->exists());

        return $code;
    }

    // =========================================================================
    // HELPER — Generate Unique Referral Code
    // Format: NEX + 6 uppercase alphanumeric chars (e.g. NEXAB12CD)
    // =========================================================================

    protected function generateReferralCode(Customer $customer): string
    {
        do {
            $code = 'NEX' . strtoupper(Str::random(6));
        } while (Customer::where('referral_code', $code)->exists());

        return $code;
    }
}
