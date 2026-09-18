<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DspPayoutRequest;
use App\Models\DspWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DspPayoutAdminController extends Controller
{
    /**
     * List all DSP Cash Redemption / Payout requests
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = DspPayoutRequest::with('dsp');

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'paid') {
            $query->where('status', 'paid');
        } elseif ($status === 'rejected') {
            $query->where('status', 'rejected');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('transaction_reference', 'like', "%{$search}%")
                  ->orWhereHas('dsp', function ($dQ) use ($search) {
                      $dQ->where('business_name', 'like', "%{$search}%")
                         ->orWhere('applicant_name', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $payouts = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_count'    => DspPayoutRequest::count(),
            'total_amount'   => DspPayoutRequest::sum('amount'),
            'pending_count'  => DspPayoutRequest::where('status', 'pending')->count(),
            'pending_amount' => DspPayoutRequest::where('status', 'pending')->sum('amount'),
            'paid_count'     => DspPayoutRequest::where('status', 'paid')->count(),
            'paid_amount'    => DspPayoutRequest::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.dsp.payouts.index', compact('payouts', 'stats', 'status', 'search'));
    }

    /**
     * Update payout request status (Mark Paid with UTR or Reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $payout = DspPayoutRequest::with('dsp')->findOrFail($id);

        $request->validate([
            'status'                => 'required|in:paid,rejected,approved',
            'transaction_reference' => 'required_if:status,paid|nullable|string|max:100',
            'admin_notes'           => 'nullable|string|max:500',
        ]);

        $adminId = Auth::guard('admin')->id();
        $newStatus = $request->status;
        $dsp = $payout->dsp;

        // If currently pending and now marked rejected, refund the deducted balance
        if ($payout->status === 'pending' && $newStatus === 'rejected') {
            $refundAmount = (float)$payout->amount;
            $newBalance = round((float)$dsp->wallet_balance + $refundAmount, 2);
            $newRedeemed = max(0, round((float)$dsp->total_redeemed - $refundAmount, 2));

            $dsp->update([
                'wallet_balance' => $newBalance,
                'total_redeemed' => $newRedeemed,
            ]);

            DspWalletTransaction::create([
                'dsp_id'           => $dsp->id,
                'amount'           => $refundAmount,
                'type'             => 'credit',
                'source'           => 'admin_adjustment',
                'reference_number' => $payout->request_number,
                'description'      => "Refund for rejected cash redemption request #{$payout->request_number}. Reason: " . ($request->admin_notes ?: 'Declined by administration.'),
                'balance_after'    => $newBalance,
            ]);
        }

        $payout->update([
            'status'                => $newStatus,
            'transaction_reference' => $request->transaction_reference ?: $payout->transaction_reference,
            'admin_notes'           => $request->admin_notes ?: $payout->admin_notes,
            'processed_by'          => $adminId,
            'processed_at'          => now(),
        ]);

        return back()->with('success', "Payout request #{$payout->request_number} updated to " . strtoupper($newStatus) . " successfully!");
    }
}
