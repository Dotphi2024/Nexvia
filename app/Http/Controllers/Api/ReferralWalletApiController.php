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
    /**
     * GET /api/customer/referral-dashboard
     * Customer referral & wallet dashboard data.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
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
            ];
        });

        // Referred Customers List
        $referredCustomers = Customer::where('referred_by_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($referee) {
                $latestReferral = Referral::where('referee_id', $referee->id)->latest()->first();
                return [
                    'id'               => $referee->id,
                    'name'             => $referee->name,
                    'phone_masked'     => $referee->phone ? (substr($referee->phone, 0, 2) . '******' . substr($referee->phone, -2)) : 'N/A',
                    'joined_at'        => $referee->created_at ? $referee->created_at->format('Y-m-d') : null,
                    'booking_status'   => $latestReferral ? ucfirst($latestReferral->status) : 'Registered',
                    'credit_earned'    => $latestReferral ? (float)$latestReferral->credit_earned : 0.00,
                    'benefit_pct'      => $latestReferral ? (float)$latestReferral->benefit_percentage : 0.00,
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
                    'available_credit' => $availableCredit,
                    'pending_credit'   => $pendingCredit,
                    'used_credit'      => $usedCredit,
                    'lifetime_credit'  => $lifetimeCredit,
                    'redeem_label'     => 'REDEEM FOR PRODUCT',
                    'can_withdraw_cash'=> false,
                ],
                'category_progress'    => $categoryProgression,
                'referred_customers'   => $referredCustomers,
                'wallet_ledger'        => $ledger,
            ],
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
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // 1. Overall Metrics across all referrals of this user
        $allReferrals = Referral::where('referrer_id', $user->id)->get();
        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);

        $totalReferralsCount = $allReferrals->where('transaction_type', 'referral')->count();
        $successfulCount     = $allReferrals->where('status', 'available')->count();
        $pendingCount        = $allReferrals->where('status', 'pending')->count();
        $cancelledCount      = $allReferrals->where('status', 'reversed')->count();

        $totalCreditedPoints  = (float) $allReferrals->where('status', 'available')->sum('credit_earned');
        $totalPendingPoints   = (float) $allReferrals->where('status', 'pending')->sum('credit_earned');
        $totalCancelledPoints = (float) $allReferrals->where('status', 'reversed')->sum('credit_earned');

        // Customer stage & cycle info
        $progress = CustomerCategoryProgress::where('user_id', $user->id)->latest()->first();
        $currentStage = $progress ? (int)$progress->current_stage : 1;
        $currentCycle = $progress ? (int)$progress->cycle_number : 1;
        $currentStageRate = ReferralStageConfig::getRateForStage($currentStage);
        $nextStage = ($currentStage >= 5) ? 1 : ($currentStage + 1);
        $nextStageRate = ReferralStageConfig::getRateForStage($nextStage);

        // 2. Query with filters
        $query = Referral::with(['referee', 'booking.product', 'category'])
            ->where('referrer_id', $user->id);

        $filterStatus = strtolower(trim((string)$request->input('status')));
        if (in_array($filterStatus, ['successful', 'available', 'credited', 'received', 'completed'])) {
            $query->where('status', 'available');
        } elseif ($filterStatus === 'pending') {
            $query->where('status', 'pending');
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

            // Check if product purchase / booking is complete
            $bookingStatus = strtolower((string) ($booking?->booking_status ?? ''));
            $paymentStatus = strtolower((string) ($booking?->payment_status ?? ''));
            $isPurchaseComplete = (
                $ref->status === 'available' ||
                in_array($bookingStatus, ['delivered', 'confirmed', 'completed']) ||
                in_array($paymentStatus, ['fully_paid', 'paid'])
            );

            $status = $ref->status; // 'available', 'pending', 'reversed'
            $isSuccessful = ($status === 'available');
            $isPending    = ($status === 'pending');
            $isCancelled  = ($status === 'reversed');

            $creditPoints = (float) $ref->credit_earned;
            $creditedReceived = $isSuccessful ? $creditPoints : 0.00;
            $pendingPoints    = $isPending ? $creditPoints : 0.00;
            $cancelledPoints  = $isCancelled ? $creditPoints : 0.00;

            $benefitPct = (float) $ref->benefit_percentage;

            if ($isSuccessful) {
                $statusBadge = 'Credit Received';
                $statusMessage = "{$benefitPct}% reward (₹" . number_format($creditPoints, 2) . ") successfully credited to wallet.";
            } elseif ($isPending) {
                $statusBadge = 'Pending Purchase Completion';
                if ($isPurchaseComplete) {
                    $statusMessage = "Product purchase completed. Reward ({$benefitPct}% = ₹" . number_format($creditPoints, 2) . ") is awaiting final qualification.";
                } else {
                    $statusMessage = "Booking placed. {$benefitPct}% reward (₹" . number_format($creditPoints, 2) . ") will be credited once product purchase and delivery are complete.";
                }
            } else {
                $statusBadge = 'Cancelled / Reversed';
                $statusMessage = "Referral credit was cancelled or reversed." . ($ref->notes ? " Reason: {$ref->notes}" : "");
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
                    'category_name'        => $ref->category?->name,
                    'mrp'                  => (float) $ref->product_value,
                    'eligible_value'       => (float) $ref->eligible_product_value,
                    'booking_amount_paid'  => $booking ? (float) $booking->booking_amount : null,
                    'balance_amount'       => $booking ? (float) $booking->balance_amount : null,
                    'payment_status'       => $booking?->payment_status ?? 'pending',
                    'booking_status'       => $booking?->booking_status ?? 'pending',
                    'is_purchase_complete' => $isPurchaseComplete,
                ],

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
