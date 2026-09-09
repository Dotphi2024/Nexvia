<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCategoryProgress;
use App\Models\Product;
use App\Models\Referral;
use App\Models\ReferralStageConfig;
use App\Models\SelfDealerWallet;
use App\Models\WalletTransaction;
use App\Services\ReferralCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SelfDealerApiController extends Controller
{
    protected ReferralCommissionService $referralService;

    public function __construct(ReferralCommissionService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * GET /api/v1/self-dealer/status
     * Get Self Dealer profile & eligibility overview.
     */
    public function status(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);

        $activationBooking = $user->activation_booking_id
            ? Booking::find($user->activation_booking_id)
            : null;

        return response()->json([
            'status' => true,
            'data'   => [
                'is_self_dealer'           => (bool) $user->is_self_dealer,
                'self_dealer_status'       => $user->self_dealer_status ?? 'inactive',
                'self_dealer_code'         => $user->self_dealer_code,
                'referral_code'            => $user->referral_code,
                'referral_url'             => $user->referral_code ? url('/ref/' . $user->referral_code) : null,
                'activated_at'             => $user->self_dealer_activated_at ? $user->self_dealer_activated_at->toIso8601String() : null,
                'activation_booking_id'    => $user->activation_booking_id,
                'activation_product_name'  => $activationBooking ? $activationBooking->product_name : null,
                'wallet'                   => [
                    'available_points' => (float) $wallet->available_points,
                    'pending_points'   => (float) $wallet->pending_points,
                    'redeemed_points'  => (float) $wallet->redeemed_points,
                    'total_earned'     => (float) $wallet->total_earned,
                ],
                'rule_version'             => 'Variant A (10% → 12% → 15% → 18% → 20% → Reset)',
            ],
        ]);
    }

    /**
     * GET /api/v1/self-dealer/categories
     * All categories with per-category 5-stage progress (Variant A).
     */
    public function categories(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $categories = Category::where('referral_eligible', true)
            ->orWhere('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $progressMap = CustomerCategoryProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('category_id');

        $stagesConfig = ReferralStageConfig::orderBy('stage_number')->get()->keyBy('stage_number');

        $data = $categories->map(function ($cat) use ($progressMap, $stagesConfig) {
            $prog = $progressMap->get($cat->id);
            $stageNumber = $prog ? (int) $prog->current_stage : 1;
            $cycleNumber = $prog ? (int) $prog->cycle_number : 1;
            $referralCount = $prog ? (int) $prog->referral_count : 0;
            $totalAllTime = $prog ? (int) $prog->total_referrals_all_time : 0;

            // Variant A rates: Stage 1=10%, 2=12%, 3=15%, 4=18%, 5=20%
            $defaultRates = [1 => 10.00, 2 => 12.00, 3 => 15.00, 4 => 18.00, 5 => 20.00];
            $currentRate = isset($stagesConfig[$stageNumber])
                ? (float) $stagesConfig[$stageNumber]->incentive_percentage
                : ($defaultRates[$stageNumber] ?? 10.00);

            $nextStageNumber = ($stageNumber >= 5) ? 1 : ($stageNumber + 1);
            $nextRate = isset($stagesConfig[$nextStageNumber])
                ? (float) $stagesConfig[$nextStageNumber]->incentive_percentage
                : ($defaultRates[$nextStageNumber] ?? 10.00);

            // Stages progression list for UI rendering
            $stagesList = [];
            for ($s = 1; $s <= 5; $s++) {
                $rate = isset($stagesConfig[$s]) ? (float) $stagesConfig[$s]->incentive_percentage : $defaultRates[$s];
                $stagesList[] = [
                    'stage'       => $s,
                    'percentage'  => $rate,
                    'is_current'  => ($s === $stageNumber),
                    'is_completed'=> ($s < $stageNumber),
                ];
            }

            return [
                'category_id'              => $cat->id,
                'category_name'            => $cat->name,
                'category_code'            => $cat->referral_category_code ?? strtoupper(substr($cat->slug, 0, 3)),
                'current_stage'            => $stageNumber,
                'max_stages'               => 5,
                'current_incentive_pct'    => $currentRate,
                'next_incentive_pct'       => $nextRate,
                'cycle_number'             => $cycleNumber,
                'referrals_in_cycle'       => $referralCount,
                'total_referrals_all_time' => $totalAllTime,
                'stages_progress'          => $stagesList,
                'is_final_stage'           => ($stageNumber === 5),
                'next_action_label'        => ($stageNumber === 5)
                    ? 'Referral 5 (20%) → Completes Cycle and Resets to Stage 1'
                    : "Next Referral earns {$currentRate}% (Stage {$stageNumber}/5)",
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    /**
     * GET /api/v1/self-dealer/category/{id}
     * Specific category progress detail.
     */
    public function categoryDetail(Request $request, $id)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $category = Category::findOrFail($id);
        $progress = CustomerCategoryProgress::where('user_id', $user->id)
            ->where('category_id', $id)
            ->first();

        $stageNumber = $progress ? (int) $progress->current_stage : 1;
        $currentRate = ReferralStageConfig::getRateForStage($stageNumber);

        return response()->json([
            'status' => true,
            'data'   => [
                'category_id'           => $category->id,
                'category_name'         => $category->name,
                'category_code'         => $category->referral_category_code,
                'current_stage'         => $stageNumber,
                'current_incentive_pct' => $currentRate,
                'cycle_number'          => $progress ? $progress->cycle_number : 1,
                'referrals_count'       => $progress ? $progress->referral_count : 0,
                'total_all_time'        => $progress ? $progress->total_referrals_all_time : 0,
            ],
        ]);
    }

    /**
     * GET /api/v1/self-dealer/wallet
     * Dedicated wallet summary endpoint.
     */
    public function wallet(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'status' => true,
            'data'   => [
                'wallet_title'             => 'MY INCENTIVE POINTS',
                'available_incentive_points'=> (float) $wallet->available_points,
                'pending_incentive_points'  => (float) $wallet->pending_points,
                'redeemed_incentive_points' => (float) $wallet->redeemed_points,
                'reversed_incentive_points' => (float) $wallet->reversed_points,
                'lifetime_earned_points'   => (float) $wallet->total_earned,
                'redemption_rules'         => [
                    'can_redeem_on_booking_balance' => true,
                    'can_withdraw_cash'            => false,
                    'points_currency_ratio'        => '1 Point = ₹1.00 Value',
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/self-dealer/wallet/transactions
     * Paginated transaction history audit log.
     */
    public function transactions(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $perPage = (int) ($request->input('per_page') ?? 20);
        $transactions = WalletTransaction::with(['category', 'booking'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

        $formatted = $transactions->getCollection()->map(function ($tx) {
            return [
                'id'                   => $tx->id,
                'transaction_type'     => $tx->transaction_type ?? $tx->source,
                'amount'               => (float) $tx->amount,
                'type'                 => $tx->type, // credit / debit
                'status'               => $tx->status, // pending, available, redeemed, reversed
                'description'          => $tx->description,
                'booking_number'       => $tx->booking ? $tx->booking->booking_number : null,
                'category_name'        => $tx->category ? $tx->category->name : null,
                'referral_stage'       => $tx->referral_stage,
                'incentive_percentage' => (float) $tx->incentive_percentage,
                'cycle_number'         => $tx->cycle_number,
                'created_at'           => $tx->created_at ? $tx->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status'     => true,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
            'data'       => $formatted,
        ]);
    }

    /**
     * GET /api/v1/self-dealer/referrals
     * My direct referrals list.
     */
    public function referrals(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $perPage = (int) ($request->input('per_page') ?? 20);
        $referrals = Referral::with(['referee', 'booking', 'category'])
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate($perPage);

        $formatted = $referrals->getCollection()->map(function ($ref) {
            $refereePhone = $ref->referee ? $ref->referee->phone : null;
            $maskedPhone  = $refereePhone
                ? substr($refereePhone, 0, 2) . '******' . substr($refereePhone, -2)
                : 'N/A';

            return [
                'referral_id'            => $ref->id,
                'referee_name'           => $ref->referee ? $ref->referee->name : 'Customer',
                'referee_phone_masked'   => $maskedPhone,
                'booking_number'         => $ref->booking ? $ref->booking->booking_number : null,
                'category_name'          => $ref->category ? $ref->category->name : null,
                'stage'                  => $ref->referral_stage,
                'cycle_number'           => $ref->cycle_number,
                'benefit_percentage'     => (float) $ref->benefit_percentage,
                'product_value'          => (float) $ref->product_value,
                'eligible_product_value' => (float) $ref->eligible_product_value,
                'credit_earned'          => (float) $ref->credit_earned,
                'status'                 => $ref->status, // pending, qualified, reversed
                'notes'                  => $ref->notes,
                'date'                   => $ref->created_at ? $ref->created_at->format('Y-m-d H:i') : null,
                'approved_at'            => $ref->approved_at ? $ref->approved_at->format('Y-m-d H:i') : null,
            ];
        });

        return response()->json([
            'status'     => true,
            'pagination' => [
                'current_page' => $referrals->currentPage(),
                'last_page'    => $referrals->lastPage(),
                'per_page'     => $referrals->perPage(),
                'total'        => $referrals->total(),
            ],
            'data'       => $formatted,
        ]);
    }

    /**
     * POST /api/v1/referral/apply
     * Validate and apply a referral code at checkout.
     */
    public function applyReferralCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Referral code is required.'], 422);
        }

        $code = strtoupper(trim($request->referral_code));
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();

        // 1. Find referrer
        $referrer = Customer::where('referral_code', $code)->first();
        if (!$referrer) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid referral code. Please check and try again.',
            ], 404);
        }

        // 2. Anti-fraud: cannot use own referral code
        if ($user && $referrer->id === $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You cannot use your own referral code.',
            ], 422);
        }

        // 3. Referrer must be active
        if (!$referrer->is_self_dealer || $referrer->self_dealer_status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'This referral code is currently inactive.',
            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Referral code applied successfully.',
            'data'    => [
                'referral_code' => $referrer->referral_code,
                'referrer_name' => $referrer->name,
                'applied'       => true,
            ],
        ]);
    }

    /**
     * GET /api/v1/products/{id}/referral-benefit
     * Calculate potential points for a product (product page benefit banner).
     */
    public function productReferralBenefit(Request $request, $id)
    {
        $product = Product::with('category')->findOrFail($id);

        $eligibleValue = (float) ($product->eligible_referral_value ?: $product->mrp);
        $activationRate = ReferralStageConfig::getActivationRate(); // 20%
        $activationPoints = $eligibleValue * ($activationRate / 100);

        // Variant A stage rates
        $stageRates = [1 => 10.00, 2 => 12.00, 3 => 15.00, 4 => 18.00, 5 => 20.00];
        $stages = [];
        foreach ($stageRates as $st => $pct) {
            $configuredPct = ReferralStageConfig::getRateForStage($st);
            $stages[] = [
                'stage'          => $st,
                'percentage'     => $configuredPct,
                'potential_points'=> $eligibleValue * ($configuredPct / 100),
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'product_id'                 => $product->id,
                'product_name'               => $product->name,
                'mrp'                        => (float) $product->mrp,
                'eligible_referral_value'    => $eligibleValue,
                'is_self_dealer_activator'   => (bool) $product->self_dealer_eligible,
                'referral_eligible'          => (bool) $product->referral_eligible,
                'self_dealer_activation'     => [
                    'activation_percentage'  => $activationRate,
                    'potential_points'       => $activationPoints,
                    'banner_text'            => "Book this product to activate Self Dealership and get ₹" . number_format($activationPoints, 2) . " ({$activationRate}%) in points!",
                ],
                'referral_stages'            => $stages,
            ],
        ]);
    }

    /**
     * POST /api/v1/self-dealer/redeem
     * Redeem available points on booking balance.
     */
    public function redeem(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'points'     => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $booking = Booking::where('id', $request->booking_id)->where('user_id', $user->id)->first();
        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
        }

        if ($booking->payment_status === 'fully_paid') {
            return response()->json(['status' => false, 'message' => 'This booking balance is already fully paid.'], 422);
        }

        $wallet = SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);
        $requestedPoints = (float) $request->points;

        if ($wallet->available_points < $requestedPoints) {
            return response()->json([
                'status'  => false,
                'message' => "Insufficient available points. You have ₹" . number_format($wallet->available_points, 2) . " available.",
            ], 422);
        }

        $balanceDue = (float) $booking->balance_amount;
        $pointsToRedeem = min($requestedPoints, $balanceDue);

        return DB::transaction(function () use ($user, $booking, $wallet, $pointsToRedeem, $balanceDue) {
            // Deduct from SelfDealerWallet
            $wallet->redeemPoints($pointsToRedeem);

            // Keep user wallet balance in sync
            $user->wallet_balance = max(0, ($user->wallet_balance ?? 0) - $pointsToRedeem);
            $user->save();

            // Record transaction
            WalletTransaction::create([
                'user_id'          => $user->id,
                'amount'           => $pointsToRedeem,
                'type'             => 'debit',
                'source'           => 'booking_redemption',
                'transaction_type' => 'redemption',
                'status'           => 'redeemed',
                'booking_id'       => $booking->id,
                'description'      => "Redeemed {$pointsToRedeem} points toward balance for booking {$booking->booking_number}",
            ]);

            // Update booking balance
            $newBalance = max(0, $balanceDue - $pointsToRedeem);
            $booking->balance_amount = $newBalance;
            if ($newBalance <= 0) {
                $booking->payment_status = 'fully_paid';
                $booking->booking_status = 'balance_paid';
            }
            $booking->save();

            return response()->json([
                'status'  => true,
                'message' => "Successfully redeemed {$pointsToRedeem} points toward your booking.",
                'data'    => [
                    'points_redeemed'          => $pointsToRedeem,
                    'remaining_balance_due'    => (float) $booking->balance_amount,
                    'booking_payment_status'   => $booking->payment_status,
                    'remaining_wallet_points'  => (float) $wallet->available_points,
                ],
            ]);
        });
    }
}
