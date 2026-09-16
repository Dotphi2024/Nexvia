<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCategoryProgress;
use App\Models\FraudFlag;
use App\Models\Referral;
use App\Models\SelfDealerWallet;
use App\Models\WalletTransaction;
use App\Services\ReferralCommissionService;
use Illuminate\Http\Request;

class SelfDealerAdminController extends Controller
{
    protected ReferralCommissionService $referralService;

    public function __construct(ReferralCommissionService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * List all Self Dealers with summary stats.
     */
    public function index(Request $request)
    {
        $query = Customer::where('is_self_dealer', true)
            ->with('selfDealerWallet')
            ->latest('self_dealer_activated_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('self_dealer_code', 'like', "%{$search}%")
                  ->orWhere('referral_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('self_dealer_status', $request->status);
        }

        $dealers = $query->paginate(20);

        $stats = [
            'total_dealers'   => Customer::where('is_self_dealer', true)->count(),
            'active_dealers'  => Customer::where('self_dealer_status', 'active')->count(),
            'total_points'    => SelfDealerWallet::sum('total_earned'),
            'pending_points'  => SelfDealerWallet::sum('pending_points'),
            'available_points'=> SelfDealerWallet::sum('available_points'),
            'redeemed_points' => SelfDealerWallet::sum('redeemed_points'),
            'fraud_flags'     => FraudFlag::where('status', 'pending_review')->count(),
        ];

        return view('admin.self_dealers.index', compact('dealers', 'stats'));
    }

    /**
     * Full Self Dealer profile — wallet, category progress, referral history.
     */
    public function show($id)
    {
        $dealer = Customer::where('is_self_dealer', true)->findOrFail($id);

        $wallet = SelfDealerWallet::where('user_id', $id)->first();

        $categoryProgress = CustomerCategoryProgress::where('user_id', $id)
            ->with('category')
            ->get();

        $referrals = Referral::where('referrer_id', $id)
            ->with(['booking', 'category', 'referee'])
            ->latest()
            ->paginate(15);

        $walletTransactions = WalletTransaction::where('user_id', $id)
            ->with('category')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.self_dealers.show', compact(
            'dealer', 'wallet', 'categoryProgress', 'referrals', 'walletTransactions'
        ));
    }

    /**
     * Manually qualify a pending referral → marks AVAILABLE, advances stage.
     */
    public function qualifyReferral(Request $request, $referralId)
    {
        $referral = Referral::findOrFail($referralId);

        if (!$referral->isPending()) {
            return back()->with('error', 'Referral is not in Pending status.');
        }

        $success = $this->referralService->qualifyReferral($referral);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? "✅ Referral #{$referralId} qualified. Points marked AVAILABLE." : 'Failed to qualify referral.'
        );
    }

    /**
     * Admin reversal of a referral (fraud / policy violation).
     */
    public function reverseReferral(Request $request, $referralId)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $referral = Referral::findOrFail($referralId);

        $success = $this->referralService->reverseReferral($referral, $request->reason);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? "⚠️ Referral #{$referralId} reversed. Points removed." : 'Failed to reverse referral.'
        );
    }

    /**
     * List all Fraud Flags for admin review.
     */
    public function fraudFlags(Request $request)
    {
        $query = FraudFlag::with(['user', 'referrer', 'booking'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $flags = $query->paginate(20);

        $pendingCount = FraudFlag::where('status', 'pending_review')->count();

        return view('admin.self_dealers.fraud_flags', compact('flags', 'pendingCount'));
    }

    /**
     * Admin clears or confirms a fraud flag.
     */
    public function reviewFraudFlag(Request $request, $flagId)
    {
        $request->validate([
            'status'      => 'required|in:cleared,confirmed_fraud',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $flag = FraudFlag::findOrFail($flagId);
        $flag->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // If confirmed fraud, reverse all pending referrals from this booking
        if ($request->status === 'confirmed_fraud' && $flag->booking_id) {
            $referrals = Referral::where('booking_id', $flag->booking_id)->where('status', 'pending')->get();
            foreach ($referrals as $referral) {
                app(ReferralCommissionService::class)->reverseReferral($referral, 'Fraud confirmed by Admin');
            }
        }

        return back()->with('success', "Fraud flag updated to: {$request->status}");
    }

    /**
     * Update Self Dealer status (suspend/reactivate).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,suspended']);

        $dealer = Customer::where('is_self_dealer', true)->findOrFail($id);
        $dealer->update(['self_dealer_status' => $request->status]);

        return back()->with('success', "Self Dealer status updated to: {$request->status}");
    }

    /**
     * All referral incentive transactions with filters.
     */
    public function transactions(Request $request)
    {
        $query = WalletTransaction::with(['user', 'category', 'referral'])
            ->where('transaction_type', 'referral_incentive')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(25);

        return view('admin.self_dealers.transactions', compact('transactions'));
    }
}
