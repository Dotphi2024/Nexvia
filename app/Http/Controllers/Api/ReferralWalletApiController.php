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

        // Wallet Metrics
        $walletTransactions = WalletTransaction::where('user_id', $user->id)->get();
        $availableCredit = (float)($user->wallet_balance ?? 0);

        $lifetimeCredit = (float)$walletTransactions->where('type', 'credit')->sum('amount');
        $usedCredit     = (float)$walletTransactions->where('type', 'debit')->sum('amount');
        $pendingCredit  = (float)Referral::where('referrer_id', $user->id)->where('status', 'pending')->sum('credit_earned');

        // Category Referral Progression Cards
        $categories = Category::where('status', 'active')->get();
        $progressRecords = CustomerCategoryProgress::where('user_id', $user->id)->get()->keyBy('category_id');

        $categoryProgression = $categories->map(function ($cat) use ($progressRecords) {
            $prog = $progressRecords->get($cat->id);
            $count = $prog ? $prog->referral_count : 0;
            $tierMap = [
                0 => 10.00,
                1 => 12.00,
                2 => 15.00,
                3 => 18.00,
            ];
            $currentTier = $prog ? $prog->current_tier_percentage : 10.00;
            $nextTier    = $tierMap[$count] ?? 20.00;

            return [
                'category_id'           => $cat->id,
                'category_name'         => $cat->name,
                'category_slug'         => $cat->slug,
                'type'                  => $cat->type,
                'successful_referrals'  => $count,
                'current_benefit_pct'   => (float)$currentTier,
                'next_benefit_pct'      => (float)$nextTier,
                'progress_status'       => "Referral {$count} / Next Tier {$nextTier}%",
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
                    'phone_masked'     => substr($referee->phone, 0, 2) . '******' . substr($referee->phone, -2),
                    'joined_at'        => $referee->created_at->format('Y-m-d'),
                    'booking_status'   => $latestReferral ? 'Booked' : 'Registered',
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
                    'description' => $tx->description,
                    'date'        => $tx->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => [
                'referral_code'       => $user->referral_code,
                'referral_url'        => $referralUrl,
                'qr_code_data'        => $qrCodeData,
                'wallet'              => [
                    'available_credit' => $availableCredit,
                    'pending_credit'   => $pendingCredit,
                    'used_credit'      => $usedCredit,
                    'lifetime_credit'  => $lifetimeCredit,
                    'redeem_label'     => 'REDEEM FOR PRODUCT',
                    'can_withdraw_cash'=> false,
                ],
                'category_progress'   => $categoryProgression,
                'referred_customers'  => $referredCustomers,
                'wallet_ledger'       => $ledger,
            ],
        ]);
    }
}
