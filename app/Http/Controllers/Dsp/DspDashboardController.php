<?php

namespace App\Http\Controllers\Dsp;

use App\Http\Controllers\Controller;
use App\Models\DspApplication;
use App\Models\Delivery;
use App\Models\Booking;
use App\Models\DspWalletTransaction;
use App\Models\DspPayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DspDashboardController extends Controller
{
    /**
     * Resolve the logged-in DSP partner.
     */
    protected function getDsp(): DspApplication
    {
        return Auth::guard('dsp')->user();
    }

    /**
     * DSP Partner Main Dashboard
     */
    public function dashboard()
    {
        $dsp = $this->getDsp();

        $servicedPincodes = $dsp->servicedPincodesArray();

        // Deliveries explicitly assigned to this DSP
        $assignedDeliveriesQuery = Delivery::with(['booking.product', 'order'])
            ->where(function ($q) use ($dsp, $servicedPincodes) {
                $q->where('dsp_id', $dsp->id)
                  ->orWhereHas('booking', function ($bQ) use ($servicedPincodes) {
                      if (!empty($servicedPincodes)) {
                          $bQ->whereIn('pincode', $servicedPincodes);
                      }
                  });
            });

        $totalDeliveries = (clone $assignedDeliveriesQuery)->count();
        $pendingDeliveries = (clone $assignedDeliveriesQuery)->whereIn('stage', ['order_confirmed', 'assigned', 'packed', 'dispatched'])->count();
        $outForDeliveryCount = (clone $assignedDeliveriesQuery)->where('stage', 'out_for_delivery')->count();
        $deliveredCount = (clone $assignedDeliveriesQuery)->where('stage', 'delivered')->count();

        $recentDeliveries = (clone $assignedDeliveriesQuery)->orderBy('id', 'desc')->take(6)->get();

        $recentTransactions = $dsp->walletTransactions()->take(5)->get();

        return view('dsp.portal.dashboard', compact(
            'dsp',
            'totalDeliveries',
            'pendingDeliveries',
            'outForDeliveryCount',
            'deliveredCount',
            'recentDeliveries',
            'recentTransactions'
        ));
    }

    /**
     * List all deliveries for this DSP
     */
    public function deliveries(Request $request)
    {
        $dsp = $this->getDsp();
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $servicedPincodes = $dsp->servicedPincodesArray();

        $query = Delivery::with(['booking.product', 'order'])
            ->where(function ($q) use ($dsp, $servicedPincodes) {
                $q->where('dsp_id', $dsp->id)
                  ->orWhereHas('booking', function ($bQ) use ($servicedPincodes) {
                      if (!empty($servicedPincodes)) {
                          $bQ->whereIn('pincode', $servicedPincodes);
                      }
                  });
            });

        if ($status === 'pending') {
            $query->whereIn('stage', ['order_confirmed', 'assigned', 'packed', 'dispatched']);
        } elseif ($status === 'out_for_delivery') {
            $query->where('stage', 'out_for_delivery');
        } elseif ($status === 'delivered') {
            $query->where('stage', 'delivered');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('booking', function ($bQ) use ($search) {
                      $bQ->where('booking_number', 'like', "%{$search}%")
                         ->orWhere('customer_name', 'like', "%{$search}%")
                         ->orWhere('customer_phone', 'like', "%{$search}%")
                         ->orWhere('pincode', 'like', "%{$search}%");
                  });
            });
        }

        $deliveries = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('dsp.portal.deliveries', compact('dsp', 'deliveries', 'status', 'search'));
    }

    /**
     * Show single delivery details
     */
    public function deliveryDetail($id)
    {
        $dsp = $this->getDsp();
        $servicedPincodes = $dsp->servicedPincodesArray();

        $delivery = Delivery::with(['booking.product', 'order'])
            ->where('id', $id)
            ->where(function ($q) use ($dsp, $servicedPincodes) {
                $q->where('dsp_id', $dsp->id)
                  ->orWhereHas('booking', function ($bQ) use ($servicedPincodes) {
                      if (!empty($servicedPincodes)) {
                          $bQ->whereIn('pincode', $servicedPincodes);
                      }
                  });
            })
            ->firstOrFail();

        // Calculate potential 5% commission
        $productAmount = $delivery->booking?->mrp ?? $delivery->order?->total_amount ?? 0.00;
        $potentialCommission = round($productAmount * 0.05, 2);

        return view('dsp.portal.delivery_show', compact('dsp', 'delivery', 'potentialCommission'));
    }

    /**
     * Update delivery status (e.g. Out for Delivery, or Complete Delivery)
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
        $dsp = $this->getDsp();
        $servicedPincodes = $dsp->servicedPincodesArray();

        $delivery = Delivery::with(['booking.product', 'order'])
            ->where('id', $id)
            ->where(function ($q) use ($dsp, $servicedPincodes) {
                $q->where('dsp_id', $dsp->id)
                  ->orWhereHas('booking', function ($bQ) use ($servicedPincodes) {
                      if (!empty($servicedPincodes)) {
                          $bQ->whereIn('pincode', $servicedPincodes);
                      }
                  });
            })
            ->firstOrFail();

        $request->validate([
            'stage'          => 'required|in:out_for_delivery,delivered',
            'delivery_notes' => 'nullable|string|max:500',
            'delivery_otp'   => 'nullable|string|max:10',
        ]);

        $newStage = $request->input('stage');

        // Assign to this DSP if not already assigned
        if (empty($delivery->dsp_id)) {
            $delivery->dsp_id = $dsp->id;
        }

        if (!empty($request->delivery_notes)) {
            $delivery->delivery_notes = $request->delivery_notes;
        }

        if ($newStage === 'out_for_delivery') {
            $delivery->stage = 'out_for_delivery';
            $delivery->dispatched_at = $delivery->dispatched_at ?: now();
            $delivery->save();

            if ($delivery->booking) {
                $delivery->booking->dsp_id = $dsp->id;
                $delivery->booking->booking_status = 'out_for_delivery';
                $delivery->booking->save();
            }

            return back()->with('success', 'Order status updated to OUT FOR DELIVERY. Keep the customer updated!');
        }

        if ($newStage === 'delivered') {
            if ($delivery->stage === 'delivered' && $delivery->dsp_commission_status === 'credited') {
                return back()->with('info', 'This order is already marked as DELIVERED and 5% commission has been credited.');
            }

            $delivery->stage = 'delivered';
            $delivery->delivered_at = now();
            $delivery->save();

            if ($delivery->booking) {
                $delivery->booking->dsp_id = $dsp->id;
                $delivery->booking->booking_status = 'completed';
                $delivery->booking->save();
            }

            // Calculate 5% Commission of Product Amount
            $productAmount = (float)($delivery->booking?->mrp ?? $delivery->order?->total_amount ?? 0.00);
            $commissionTx = $dsp->creditDeliveryCommission($productAmount, $delivery, $delivery->booking);

            return back()->with('success', "Order successfully marked as DELIVERED! 5% Commission of ₹" . number_format($commissionTx->amount, 2) . " has been credited to your cash-redeemable wallet!");
        }

        return back();
    }

    /**
     * DSP Wallet & Cash Redemption Ledger
     */
    public function wallet()
    {
        $dsp = $this->getDsp();

        $transactions = $dsp->walletTransactions()->paginate(15);
        $payoutRequests = $dsp->payoutRequests()->take(10)->get();

        return view('dsp.portal.wallet', compact('dsp', 'transactions', 'payoutRequests'));
    }

    /**
     * Submit Cash Redemption Request
     */
    public function requestPayout(Request $request)
    {
        $dsp = $this->getDsp();

        $request->validate([
            'amount'              => 'required|numeric|min:100',
            'payout_mode'         => 'required|in:bank,upi',
            'bank_account_number' => 'required_if:payout_mode,bank|nullable|string|max:50',
            'bank_ifsc'           => 'required_if:payout_mode,bank|nullable|string|max:20',
            'bank_name'           => 'required_if:payout_mode,bank|nullable|string|max:100',
            'bank_holder_name'    => 'required_if:payout_mode,bank|nullable|string|max:150',
            'upi_id'              => 'required_if:payout_mode,upi|nullable|string|max:100',
        ]);

        $redeemAmount = round((float)$request->amount, 2);

        if ($redeemAmount > (float)$dsp->wallet_balance) {
            return back()->withErrors([
                'amount' => 'Requested cash redemption amount (₹' . number_format($redeemAmount, 2) . ') exceeds your available balance of ₹' . number_format($dsp->wallet_balance, 2),
            ]);
        }

        // Deduct from wallet balance
        $newBalance = round((float)$dsp->wallet_balance - $redeemAmount, 2);
        $newRedeemed = round((float)$dsp->total_redeemed + $redeemAmount, 2);

        $dsp->update([
            'wallet_balance'        => $newBalance,
            'total_redeemed'        => $newRedeemed,
            'payout_account_number' => $request->bank_account_number ?: $dsp->payout_account_number,
            'payout_ifsc'           => $request->bank_ifsc ? strtoupper($request->bank_ifsc) : $dsp->payout_ifsc,
            'payout_bank_name'      => $request->bank_name ?: $dsp->payout_bank_name,
            'payout_holder_name'    => $request->bank_holder_name ?: $dsp->payout_holder_name,
            'payout_upi_id'         => $request->upi_id ?: $dsp->payout_upi_id,
        ]);

        $requestNumber = 'DSP-PAY-' . date('Ymd') . '-' . rand(1000, 9999);

        // Create Payout Request
        $payout = DspPayoutRequest::create([
            'dsp_id'              => $dsp->id,
            'request_number'      => $requestNumber,
            'amount'              => $redeemAmount,
            'payout_mode'         => $request->payout_mode,
            'bank_account_number' => $request->bank_account_number ?: $dsp->payout_account_number,
            'bank_ifsc'           => $request->bank_ifsc ? strtoupper($request->bank_ifsc) : $dsp->payout_ifsc,
            'bank_name'           => $request->bank_name ?: $dsp->payout_bank_name,
            'bank_holder_name'    => $request->bank_holder_name ?: $dsp->payout_holder_name,
            'upi_id'              => $request->upi_id ?: $dsp->payout_upi_id,
            'status'              => 'pending',
        ]);

        // Log Debit Transaction
        DspWalletTransaction::create([
            'dsp_id'           => $dsp->id,
            'amount'           => $redeemAmount,
            'type'             => 'debit',
            'source'           => 'cash_redemption',
            'reference_number' => $requestNumber,
            'description'      => "Cash redemption payout request #{$requestNumber} submitted to " . ($request->payout_mode === 'upi' ? "UPI: {$request->upi_id}" : "Bank A/c: {$request->bank_account_number}"),
            'balance_after'    => $newBalance,
        ]);

        return back()->with('success', "Cash redemption request #{$requestNumber} for ₹" . number_format($redeemAmount, 2) . " submitted successfully! You will receive notification upon bank remittance.");
    }

    /**
     * DSP Territory & Profile Details
     */
    public function profile()
    {
        $dsp = $this->getDsp();
        return view('dsp.portal.profile', compact('dsp'));
    }

    /**
     * Update DSP Payout Details or Change Password
     */
    public function updateProfile(Request $request)
    {
        $dsp = $this->getDsp();

        $request->validate([
            'payout_account_number' => 'nullable|string|max:50',
            'payout_ifsc'           => 'nullable|string|max:20',
            'payout_bank_name'      => 'nullable|string|max:100',
            'payout_holder_name'    => 'nullable|string|max:150',
            'payout_upi_id'         => 'nullable|string|max:100',
            'current_password'      => 'nullable|required_with:new_password',
            'new_password'          => 'nullable|min:6|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (!empty($dsp->password) && !Hash::check($request->current_password, $dsp->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $dsp->password = Hash::make($request->new_password);
        }

        $dsp->payout_account_number = $request->payout_account_number ?: $dsp->payout_account_number;
        $dsp->payout_ifsc           = $request->payout_ifsc ? strtoupper($request->payout_ifsc) : $dsp->payout_ifsc;
        $dsp->payout_bank_name      = $request->payout_bank_name ?: $dsp->payout_bank_name;
        $dsp->payout_holder_name    = $request->payout_holder_name ?: $dsp->payout_holder_name;
        $dsp->payout_upi_id         = $request->payout_upi_id ?: $dsp->payout_upi_id;
        $dsp->save();

        return back()->with('success', 'Profile and payout bank settings updated successfully.');
    }
}
