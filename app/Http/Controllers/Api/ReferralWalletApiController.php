<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Referral;
use App\Models\CustomerCategoryProgress;
use App\Models\WalletTransaction;
use App\Models\SelfDealerWallet;
use App\Models\ReferralStageConfig;
use Illuminate\Http\Request;

class ReferralWalletApiController extends Controller
{
    protected function resolveUser(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $user = Customer::where('api_token', $token)->first();
            }

            if (!$user) {
                $userId = $request->query('user_id')
                    ?? $request->input('user_id')
                    ?? $request->header('user_id')
                    ?? $request->header('X-User-Id')
                    ?? ($bodyJson['user_id'] ?? null);
                if (!empty($userId)) {
                    $user = Customer::find($userId);
                }
            }
        }
        return $user;
    }

    /**
     * GET /api/customer/referral-dashboard
     * Customer referral & wallet dashboard data.
     */
    public function dashboard(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = 'NEX-' . strtoupper(\Illuminate\Support\Str::random(6));
            $user->save();
        }

        $referralUrl = url('/ref/' . $user->referral_code);
        $qrCodeData = 'NEXVIA_REF:' . $user->referral_code;

        // Wallet Metrics from dedicated SelfDealerWallet
        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);
        $availableCredit = (float) ($wallet->available_points ?? $user->wallet_balance ?? 0);
        $pendingCredit   = (float) ($wallet->pending_points ?? 0);
        $usedCredit      = (float) ($wallet->redeemed_points ?? 0);
        $lifetimeCredit  = (float) ($wallet->total_earned ?? 0);

        // Separate breakdown for own purchase activation points vs third-party referral commission
        $activationCredit = (float) WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->whereIn('source', ['self_dealer_activation'])
            ->where('status', 'available')
            ->sum('amount');

        $referralCommission = (float) WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
            ->where('status', 'available')
            ->sum('amount');

        // Category Referral Progression Cards (Variant A 5-stage cycle)
        $categories = Category::where('is_active', true)->orWhere('referral_eligible', true)->get();
        $progressRecords = CustomerCategoryProgress::where('user_id', $user->id)->get()->keyBy('category_id');

        $categoryProgression = $categories->map(function ($cat) use ($progressRecords) {
            $prog = $progressRecords->get($cat->id);
            $currentStage = $prog ? (int)$prog->current_stage : 1;
            $cycleNumber  = $prog ? (int)$prog->cycle_number : 1;
            $count        = $prog ? (int)$prog->referral_count : 0;
            $totalAllTime = $prog ? (int)$prog->total_referrals_all_time : 0;

            $currentRate = ReferralStageConfig::getRateForStage($currentStage);
            $nextStage   = ($currentStage >= 5) ? 1 : ($currentStage + 1);
            $nextRate    = ReferralStageConfig::getRateForStage($nextStage);

            $stagesProgress = [];
            for ($s = 1; $s <= 5; $s++) {
                $stagesProgress[] = [
                    'stage'        => $s,
                    'percentage'   => (float) ReferralStageConfig::getRateForStage($s),
                    'is_current'   => ($s === $currentStage),
                    'is_completed' => ($s < $currentStage),
                ];
            }

            return [
                'category_id'              => $cat->id,
                'category_name'            => $cat->name,
                'category_slug'            => $cat->slug,
                'category_code'            => $cat->referral_category_code ?? 'CAT',
                'type'                     => $cat->type,
                'current_stage'            => $currentStage,
                'max_stages'               => 5,
                'cycle_number'             => $cycleNumber,
                'successful_referrals'     => $count,
                'total_referrals_all_time' => $totalAllTime,
                'current_benefit_pct'      => (float)$currentRate,
                'next_benefit_pct'         => (float)$nextRate,
                'progress_status'          => "Stage {$currentStage}/5 ({$currentRate}%) • Cycle #{$cycleNumber}",
                'stages_progress'          => $stagesProgress,
            ];
        });

        // Referred Customers List
        $referredCustomers = Customer::where('referred_by_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($referee) {
                $latestReferral = Referral::with(['category', 'booking.product'])
                    ->where('referee_id', $referee->id)
                    ->where('transaction_type', 'referral')
                    ->latest()
                    ->first();
                $creditEarned = 0.00;
                $potentialCredit = 0.00;
                $pendingCredit = 0.00;
                $isFullySettled = false;
                $baseMrp = 0.00;

                if ($latestReferral) {
                    $baseMrp = (float)($latestReferral->eligible_product_value > 0 ? $latestReferral->eligible_product_value : $latestReferral->product_value);
                    $rate    = (float)$latestReferral->benefit_percentage;
                    $potentialCredit = round($baseMrp * ($rate / 100), 2);

                    $booking = $latestReferral->booking;
                    $bookingBalance = (float)($booking?->balance_amount ?? 0);
                    $isFullySettled = ($latestReferral->status === 'available') && (($booking?->payment_status === 'fully_paid') || $bookingBalance <= 0);

                    // Credit earned MUST reflect the actual commission transferred to date for paid transactions!
                    $actualCredited = (float) WalletTransaction::where('user_id', $latestReferral->referrer_id)
                        ->where('booking_id', $latestReferral->booking_id)
                        ->where('type', 'credit')
                        ->where('status', 'available')
                        ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
                        ->sum('amount');

                    if ($actualCredited <= 0 && (float)$latestReferral->credit_earned > 0) {
                        $actualCredited = (float)$latestReferral->credit_earned;
                    }

                    if ($isFullySettled) {
                        $creditEarned = $potentialCredit > 0 ? $potentialCredit : $actualCredited;
                        $pendingCredit = 0.00;
                    } else {
                        $creditEarned = min($potentialCredit, $actualCredited);
                        $pendingCredit = max(0.00, round($potentialCredit - $creditEarned, 2));
                    }
                }

                $statusLabel = 'Registered';
                if ($latestReferral) {
                    if ($latestReferral->status === 'reversed') {
                        $statusLabel = 'Cancelled';
                    } elseif ($isFullySettled) {
                        $statusLabel = '100% Fully Settled';
                    } elseif ($latestReferral->status === 'partial' || $pendingCredit > 0) {
                        $statusLabel = 'Transactions In Progress (₹' . number_format($creditEarned, 2) . ' credited, ₹' . number_format($pendingCredit, 2) . ' locked)';
                    } else {
                        $statusLabel = ucfirst($latestReferral->status);
                    }
                }

                return [
                    'id'                       => $referee->id,
                    'name'                     => $referee->name,
                    'phone_masked'             => $referee->phone ? (substr($referee->phone, 0, 2) . '******' . substr($referee->phone, -2)) : 'N/A',
                    'joined_at'                => $referee->created_at ? $referee->created_at->format('Y-m-d') : null,
                    'booking_status'           => $statusLabel,
                    'credit_earned'            => $creditEarned,
                    'total_potential_credit'   => $potentialCredit,
                    'pending_credit'           => $pendingCredit,
                    'is_fully_settled'         => $isFullySettled,
                    'benefit_pct'              => $latestReferral ? (float)$latestReferral->benefit_percentage : 0.00,
                    'category_name'            => $latestReferral?->category?->name ?? 'Nexvia Booking',
                    'booking_number'           => $latestReferral?->booking?->booking_number ?? '',
                    'stage'                    => $latestReferral?->referral_stage ?? 1,
                    'stage_label'              => $latestReferral?->referral_stage ? "Stage {$latestReferral->referral_stage}" : 'Stage 1',
                    'product_value'            => $baseMrp,
                    'credit_points'            => $potentialCredit,
                    'credited_points_received' => $creditEarned,
                    'pending_points'           => $pendingCredit,
                    'status'                   => $latestReferral?->status ?? 'registered',
                ];
            });

        // Transaction History Ledger
        $ledger = WalletTransaction::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(20)
            ->get()
            ->map(function ($tx) {
                return [
                    'id'          => $tx->id,
                    'amount'      => (float)$tx->amount,
                    'type'        => $tx->type,
                    'source'      => $tx->source,
                    'status'      => $tx->status ?? 'available',
                    'stage'       => $tx->referral_stage,
                    'percentage'  => (float)$tx->incentive_percentage,
                    'description' => $tx->description,
                    'date'        => $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => [
                'is_self_dealer'       => (bool) $user->is_self_dealer,
                'self_dealer_code'     => $user->self_dealer_code,
                'referral_code'        => $user->referral_code,
                'referral_url'         => $referralUrl,
                'qr_code_data'         => $qrCodeData,
                'wallet'               => [
                    'available_credit'            => $availableCredit,
                    'pending_credit'              => $pendingCredit,
                    'used_credit'                 => $usedCredit,
                    'lifetime_credit'             => $lifetimeCredit,
                    'activation_credit_earned'    => $activationCredit,
                    'referral_commission_earned'  => $referralCommission,
                    'redeem_label'                => 'REDEEM FOR PRODUCT',
                    'can_withdraw_cash'           => false,
                ],
                'available_credit'            => $availableCredit,
                'pending_credit'              => $pendingCredit,
                'activation_credit_earned'    => $activationCredit,
                'referral_commission_earned'  => $referralCommission,
                'category_progress'    => $categoryProgression,
                'referred_customers'   => $referredCustomers,
                'wallet_ledger'        => $ledger,
                'transactions'         => $ledger,
                'recent_transactions'  => $ledger,
            ],
            'transactions' => $ledger,
        ]);
    }

    /**
     * GET /api/customer/referrals
     * Detailed referral performance, status, percentage, and points ledger.
     *
     * Provides:
     * - List of referred customers & purchases
     * - Confirmation whether referral is successful (reward received) or pending product purchase completion
     * - Tier percentage applied (10%, 12%, 15%, 18%, 20% or 20% activation)
     * - Exact credited points received vs pending points vs cancelled/reversed points
     * - High-level summary counters
     */
    public function referrals(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        // 1. Overall Metrics across all referrals of this user (strictly clients to whom referred)
        $allReferrals = Referral::where('referrer_id', $user->id)
            ->where('referee_id', '!=', $user->id)
            ->where('transaction_type', 'referral')
            ->get();
        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);

        $totalReferralsCount = $allReferrals->count();
        $successfulCount     = $allReferrals->where('status', 'available')->count();
        $pendingCount        = $allReferrals->whereIn('status', ['pending', 'partial'])->count();
        $cancelledCount      = $allReferrals->where('status', 'reversed')->count();

        $totalCreditedPoints  = 0.0;
        $totalPendingPoints   = 0.0;
        $totalCancelledPoints = 0.0;

        $activationCredit = (float) WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->whereIn('source', ['self_dealer_activation'])
            ->where('status', 'available')
            ->sum('amount');

        $referralCommission = (float) WalletTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
            ->where('status', 'available')
            ->sum('amount');

        foreach ($allReferrals as $r) {
            $rBaseMrp = (float)($r->eligible_product_value > 0 ? $r->eligible_product_value : $r->product_value);
            $rBenefit = (float)$r->benefit_percentage;
            $rTotalComm = round($rBaseMrp * ($rBenefit / 100), 2);
            $rBooking = $r->booking;
            $rFullyPaid = ($r->status === 'available') && (!$rBooking || $rBooking->payment_status === 'fully_paid' || (float)$rBooking->balance_amount <= 0);

            $rActualCredited = (float) WalletTransaction::where('user_id', $r->referrer_id)
                ->where('booking_id', $r->booking_id)
                ->where('type', 'credit')
                ->where('status', 'available')
                ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
                ->sum('amount');
            if ($rActualCredited <= 0 && (float)$r->credit_earned > 0) {
                $rActualCredited = (float)$r->credit_earned;
            }

            if ($r->status === 'reversed') {
                $totalCancelledPoints += $rTotalComm;
            } elseif ($rFullyPaid) {
                $totalCreditedPoints += ($rTotalComm > 0 ? $rTotalComm : $rActualCredited);
            } else {
                $credited = min($rTotalComm, $rActualCredited);
                $totalCreditedPoints += $credited;
                $totalPendingPoints  += max(0.00, round($rTotalComm - $credited, 2));
            }
        }

        // Customer stage & cycle info
        $progress = CustomerCategoryProgress::where('user_id', $user->id)->latest()->first();
        $currentStage = $progress ? (int)$progress->current_stage : 1;
        $currentCycle = $progress ? (int)$progress->cycle_number : 1;
        $currentStageRate = ReferralStageConfig::getRateForStage($currentStage);
        $nextStage = ($currentStage >= 5) ? 1 : ($currentStage + 1);
        $nextStageRate = ReferralStageConfig::getRateForStage($nextStage);

        // 2. Query with filters (only third-party clients to whom referred, never the logged-in user themselves)
        $query = Referral::with(['referee', 'booking.product', 'category'])
            ->where('referrer_id', $user->id)
            ->where('referee_id', '!=', $user->id)
            ->where('transaction_type', 'referral');

        $filterStatus = strtolower(trim((string)$request->input('status')));
        if (in_array($filterStatus, ['successful', 'available', 'credited', 'received', 'completed'])) {
            $query->where('status', 'available');
        } elseif (in_array($filterStatus, ['pending', 'partial', 'in_progress', 'active'])) {
            $query->whereIn('status', ['pending', 'partial']);
        } elseif (in_array($filterStatus, ['cancelled', 'reversed', 'rejected'])) {
            $query->where('status', 'reversed');
        }

        $filterType = strtolower(trim((string)$request->input('type')));
        if (in_array($filterType, ['referral', 'activation'])) {
            $query->where('transaction_type', $filterType);
        }

        $perPage = max(1, min(100, (int) ($request->input('per_page') ?? 20)));
        $paginated = $query->latest('id')->paginate($perPage);

        $items = $paginated->getCollection()->map(function ($ref) {
            $booking = $ref->booking;
            $product = $booking?->product;
            $referee = $ref->referee;

            $refereePhone = $referee?->phone;
            $maskedPhone  = $refereePhone
                ? substr($refereePhone, 0, 2) . '******' . substr($refereePhone, -2)
                : 'N/A';

            $bookingStatus = strtolower((string) ($booking?->booking_status ?? ''));
            $paymentStatus = strtolower((string) ($booking?->payment_status ?? ''));
            $bookingBalance = (float) ($booking?->balance_amount ?? 0);
            $isFullyPaidOrZeroBal = ($paymentStatus === 'fully_paid') || ($bookingBalance <= 0);

            $isPurchaseComplete = (
                in_array($bookingStatus, ['delivered', 'completed']) ||
                ($isFullyPaidOrZeroBal && $ref->status === 'available')
            );

            $status = $ref->status; // 'available', 'pending', 'partial', 'reversed'
            $isCancelled = ($status === 'reversed');

            $baseMrp = (float) ($ref->eligible_product_value > 0 
                ? $ref->eligible_product_value 
                : ($ref->product_value > 0 ? $ref->product_value : ($product?->mrp ?: $booking?->mrp)));
            $benefitPct = (float) $ref->benefit_percentage;
            $totalCommission = round($baseMrp * ($benefitPct / 100), 2);

            // Pricing & Tax Breakdown
            $catType = strtolower($product?->category?->type ?? '');
            $catName = strtolower($product?->category?->name ?? '');
            $isDlsAgro = ($catType === 'dls_farm_equipment' || $catType === 'dls_agro' || $catType === 'agro' || $catType === 'farm')
                || str_contains($catName, 'farm')
                || str_contains($catName, 'agro')
                || str_contains(strtolower($product?->name ?? ''), 'cultivator');
            $taxRate = $isDlsAgro ? 5.0 : 18.0;

            if ($booking && (float)$booking->mrp > $baseMrp) {
                $totalPriceWithTax = (float)$booking->mrp;
                $taxAmount = round($totalPriceWithTax - $baseMrp, 2);
            } else {
                $taxAmount = round($baseMrp * ($taxRate / 100), 2);
                $totalPriceWithTax = round($baseMrp + $taxAmount, 2);
            }

            // 20% advance breakdown (actual base paid excluding GST + GST paid)
            $advanceBasePaid = round($baseMrp * 0.20, 2);
            $advanceGstPaid  = round($taxAmount * 0.20, 2);
            $advanceTotalPaid = round($advanceBasePaid + $advanceGstPaid, 2);
            if ($booking && (float)$booking->booking_amount > 0) {
                $actualPaid = (float)$booking->booking_amount;
                if (abs($actualPaid - $advanceTotalPaid) <= 2.0) {
                    $advanceTotalPaid = $actualPaid;
                    $advanceGstPaid   = round($advanceTotalPaid - $advanceBasePaid, 2);
                }
            }

            // 80% balance breakdown (actual base due excluding GST + GST due)
            $balanceBaseDue = round($baseMrp * 0.80, 2);
            $balanceGstDue  = round($taxAmount - $advanceGstPaid, 2);
            $balanceTotalDue = round($balanceBaseDue + $balanceGstDue, 2);
            if ($isFullyPaidOrZeroBal) {
                $balanceBaseDue  = 0.00;
                $balanceGstDue   = 0.00;
                $balanceTotalDue = 0.00;
            } elseif ($booking && $booking->balance_amount !== null) {
                $actualBal = (float)$booking->balance_amount;
                if (abs($actualBal - $balanceTotalDue) <= 2.0) {
                    $balanceTotalDue = $actualBal;
                    $balanceGstDue   = round($balanceTotalDue - $balanceBaseDue, 2);
                }
            }

            // Compute actual commission transferred so far for this referral
            $actualCredited = (float) WalletTransaction::where('user_id', $ref->referrer_id)
                ->where('booking_id', $ref->booking_id)
                ->where('type', 'credit')
                ->where('status', 'available')
                ->whereIn('source', ['referral_incentive', 'category_completion_reward', 'referral_balance_settlement'])
                ->sum('amount');

            if ($actualCredited <= 0 && (float)$ref->credit_earned > 0) {
                $actualCredited = (float)$ref->credit_earned;
            }

            if ($isCancelled) {
                $isSuccessful = false;
                $isPending    = false;
                $creditPoints = $totalCommission > 0 ? $totalCommission : (float) $ref->credit_earned;
                $creditedReceived = 0.00;
                $pendingPoints    = 0.00;
                $cancelledPoints  = $creditPoints;
                $statusBadge = 'Cancelled / Reversed';
                $statusMessage = "Referral credit was cancelled or reversed." . ($ref->notes ? " Reason: {$ref->notes}" : "");
            } elseif ($isFullyPaidOrZeroBal && ($ref->status === 'available' || $actualCredited >= $totalCommission)) {
                $isSuccessful = true;
                $isPending    = false;
                $creditPoints = $totalCommission > 0 ? $totalCommission : (float) $ref->credit_earned;
                $creditedReceived = $creditPoints;
                $pendingPoints    = 0.00;
                $cancelledPoints  = 0.00;
                $statusBadge = 'Credit Received';
                $statusMessage = "{$benefitPct}% reward (₹" . number_format($creditPoints, 2) . ") 100% fully settled and credited to wallet.";
            } else {
                $isSuccessful = false;
                $isPending    = true;
                $creditPoints = $totalCommission > 0 ? $totalCommission : (float) $ref->credit_earned;
                $creditedReceived = min($creditPoints, $actualCredited);
                $pendingPoints    = max(0.00, round($creditPoints - $creditedReceived, 2));
                $cancelledPoints  = 0.00;
                $statusBadge = ($creditedReceived > 0) ? 'Partial Settlement' : 'Active 60-Day Lock';
                $statusMessage = "Booking in progress. {$benefitPct}% reward: ₹" . number_format($creditedReceived, 2) . " credited for paid transactions; ₹" . number_format($pendingPoints, 2) . " locked pending balance completion.";
            }

            return [
                'id'                       => $ref->id,
                'referral_id'              => $ref->id,
                'transaction_type'         => $ref->transaction_type, // referral or activation
                'transaction_type_label'   => $ref->transaction_type === 'activation' ? 'Self-Dealer Activation (Own Purchase)' : 'Referral Reward',
                
                // Referee details
                'referee' => [
                    'id'           => $referee?->id,
                    'name'         => $referee?->name ?? 'Customer',
                    'phone_masked' => $maskedPhone,
                    'joined_at'    => $referee?->created_at?->format('Y-m-d H:i:s'),
                ],

                // Product & Booking Purchase Details
                'booking' => [
                    'id'                   => $booking?->id,
                    'booking_number'       => $booking?->booking_number,
                    'product_id'           => $booking?->product_id ?? $product?->id,
                    'product_name'         => $booking?->product_name ?? $product?->name ?? 'Nexvia Product',
                    'product_image'        => $product?->main_image,
                    'category_name'        => $ref->category?->name,
                    'mrp'                  => $baseMrp,
                    'eligible_value'       => $baseMrp,
                    'base_price'           => $baseMrp,
                    'tax_amount'           => $taxAmount,
                    'tax_rate_pct'         => $taxRate,
                    'total_price_with_tax' => $totalPriceWithTax,
                    'advance_base_paid'    => $advanceBasePaid,
                    'advance_gst_paid'     => $advanceGstPaid,
                    'advance_total_paid'   => $advanceTotalPaid,
                    'balance_base_due'     => $balanceBaseDue,
                    'balance_gst_due'      => $balanceGstDue,
                    'balance_total_due'    => $balanceTotalDue,
                    'booking_amount_paid'  => $booking ? (float) $booking->booking_amount : $advanceTotalPaid,
                    'balance_amount'       => $booking ? (float) $booking->balance_amount : $balanceTotalDue,
                    'payment_status'       => $booking?->payment_status ?? 'pending',
                    'booking_status'       => $booking?->booking_status ?? 'pending',
                    'is_purchase_complete' => $isPurchaseComplete,
                ],

                // Pricing & GST breakdown exposed at top level
                'base_price'               => $baseMrp,
                'tax_amount'               => $taxAmount,
                'tax_rate_pct'             => $taxRate,
                'total_price_with_tax'     => $totalPriceWithTax,
                'advance_base_paid'        => $advanceBasePaid,
                'advance_gst_paid'         => $advanceGstPaid,
                'advance_total_paid'       => $advanceTotalPaid,
                'balance_base_due'         => $balanceBaseDue,
                'balance_gst_due'          => $balanceGstDue,
                'balance_total_due'        => $balanceTotalDue,

                // Percentage & Progression
                'percentage'               => $benefitPct,
                'percentage_label'         => "{$benefitPct}%",
                'stage'                    => $ref->referral_stage,
                'stage_label'              => $ref->referral_stage ? "Stage {$ref->referral_stage} ({$benefitPct}%)" : "Activation ({$benefitPct}%)",
                'cycle_number'             => $ref->cycle_number,

                // Points & Credit Status
                'status'                   => $status, // available, pending, reversed
                'is_successful'            => $isSuccessful,
                'reward_received'          => $isSuccessful,
                'is_pending'               => $isPending,
                'is_cancelled'             => $isCancelled,
                'credit_points'            => $creditPoints,
                'credited_points_received' => $creditedReceived,
                'pending_points'           => $pendingPoints,
                'cancelled_points'         => $cancelledPoints,

                // Status presentation
                'status_badge'             => $statusBadge,
                'status_description'       => $statusMessage,
                'approved_at'              => $ref->approved_at ? $ref->approved_at->format('Y-m-d H:i:s') : null,
                'reversed_at'              => $ref->reversed_at ? $ref->reversed_at->format('Y-m-d H:i:s') : null,
                'cancellation_reason'      => $isCancelled ? $ref->notes : null,
                'notes'                    => $ref->notes,
                'created_at'               => $ref->created_at ? $ref->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'     => true,
            'summary'    => [
                'total_referrals_count'          => $totalReferralsCount,
                'successful_referrals_count'     => $successfulCount,
                'pending_referrals_count'        => $pendingCount,
                'cancelled_referrals_count'      => $cancelledCount,
                'total_credited_points_received' => $totalCreditedPoints,
                'total_pending_points'           => $totalPendingPoints,
                'total_cancelled_points'         => $totalCancelledPoints,
                'activation_credit_earned'       => $activationCredit,
                'referral_commission_earned'     => $referralCommission,
                'wallet_available_balance'       => (float) ($wallet->available_points ?? $user->wallet_balance ?? 0),
                'wallet_pending_balance'         => (float) ($wallet->pending_points ?? 0),
                'current_stage'                  => $currentStage,
                'current_stage_percentage'       => (float) $currentStageRate,
                'current_stage_label'            => "Stage {$currentStage} ({$currentStageRate}%)",
                'current_cycle_number'           => $currentCycle,
                'next_stage_percentage'          => (float) $nextStageRate,
                'referral_code'                  => $user->referral_code,
                'referral_url'                   => url('/ref/' . $user->referral_code),
            ],
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'data'       => $items,
        ]);
    }
}
