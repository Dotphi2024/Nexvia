<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Referral;
use App\Models\CustomerCategoryProgress;
use App\Models\WalletTransaction;
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
        $wallet = \App\Models\SelfDealerWallet::firstOrCreate(['user_id' => $user->id]);
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

            $currentRate = \App\Models\ReferralStageConfig::getRateForStage($currentStage);
            $nextStage   = ($currentStage >= 5) ? 1 : ($currentStage + 1);
            $nextRate    = \App\Models\ReferralStageConfig::getRateForStage($nextStage);

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
}
