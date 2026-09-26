<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DspApplication;
use App\Models\Delivery;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\UserAddress;
use App\Models\DspWalletTransaction;
use App\Models\DspPayoutRequest;
use App\Models\ServiceRequest;
use App\Services\DspMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DspApiController extends Controller
{
    /**
     * Resolve the current authenticated DSP partner from token, middleware attributes, or request.
     */
    protected function resolveDsp(Request $request): ?DspApplication
    {
        $dsp = $request->attributes->get('authenticated_dsp') ?? $request->user();

        if (!$dsp) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('token')
                ?? $request->header('token')
                ?? ($bodyJson['token'] ?? null)
                ?? ($bodyJson['api_token'] ?? null);

            if (!empty($token)) {
                $dsp = DspApplication::where('api_token', $token)->first();
            }

            if (!$dsp) {
                $dspId = $request->input('dsp_id')
                    ?? $request->input('id')
                    ?? ($bodyJson['dsp_id'] ?? null)
                    ?? ($bodyJson['id'] ?? null);

                if (!empty($dspId)) {
                    $dsp = DspApplication::find($dspId);
                }
            }
        }

        return $dsp;
    }

    /**
     * POST /api/dsp/login
     * Authenticate DSP Partner using mobile number and password
     */
    public function login(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $mobile = $request->input('mobile') ?? ($bodyJson['mobile'] ?? null);
        $password = $request->input('password') ?? ($bodyJson['password'] ?? null);

        if (empty($mobile) || empty($password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Mobile number and password are required.',
            ], 422);
        }

        $mobile = trim((string)$mobile);
        $dsp = DspApplication::where('mobile', $mobile)
            ->orWhere('whatsapp', $mobile)
            ->first();

        if (!$dsp) {
            return response()->json([
                'status'  => false,
                'message' => 'No DSP partner registered with this mobile number.',
            ], 404);
        }

        if ($dsp->status === 'rejected') {
            return response()->json([
                'status'  => false,
                'message' => 'Your DSP partner application is rejected. Please contact administrator.',
            ], 403);
        }

        $passwordValid = false;
        if (!empty($dsp->password)) {
            $passwordValid = Hash::check($password, $dsp->password);
        } else {
            // Default password check for first-time access
            $defaultPass = 'dsp@123';
            if ($password === $defaultPass || $password === substr($dsp->mobile, -6)) {
                $dsp->password = Hash::make($password);
                $dsp->save();
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid password credentials.',
            ], 401);
        }

        // Generate persistent API Token
        if (empty($dsp->api_token)) {
            $dsp->api_token = Str::random(60);
            $dsp->save();
        }

        return response()->json([
            'status'  => true,
            'message' => 'DSP Partner authenticated successfully.',
            'token'   => $dsp->api_token,
            'dsp'     => [
                'id'                     => $dsp->id,
                'application_number'     => $dsp->application_number,
                'business_name'          => $dsp->business_name ?: $dsp->applicant_name,
                'applicant_name'         => $dsp->applicant_name,
                'mobile'                 => $dsp->mobile,
                'email'                  => $dsp->email,
                'territory_area'         => $dsp->preferred_territory_area,
                'serviced_pincodes'      => $dsp->servicedPincodesArray(),
                'district'               => $dsp->district,
                'state'                  => $dsp->state,
                'status'                 => $dsp->status,
                'wallet_balance'         => (float)$dsp->wallet_balance,
                'total_earned'           => (float)$dsp->total_earned,
                'total_redeemed'         => (float)$dsp->total_redeemed,
                'commission_rate'        => '5.0%',
            ],
        ]);
    }

    /**
     * POST /api/dsp/logout
     * Invalidate DSP API session
     */
    public function logout(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if ($dsp) {
            $dsp->api_token = null;
            $dsp->save();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/dsp/dashboard
     * Get territory delivery counts, wallet balance, and recent activities
     */
    public function dashboard(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $servicedPincodes = $dsp->servicedPincodesArray();

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

        $recentDeliveries = (clone $assignedDeliveriesQuery)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($delivery) {
                $booking = $delivery->booking;
                $order = $delivery->order;
                $productPrice = (float)($booking?->mrp ?? $order?->total_amount ?? 0);
                return [
                    'id'               => $delivery->id,
                    'tracking_number'  => $delivery->tracking_number,
                    'order_number'     => $booking?->booking_number ?? $order?->order_number,
                    'product_name'     => $booking?->product_name ?? 'NEXVIA Product',
                    'customer_name'    => $booking?->customer_name ?? $order?->customer_name,
                    'customer_phone'   => $booking?->customer_phone ?? $order?->customer_phone,
                    'shipping_pincode' => $booking?->pincode ?? $order?->pincode,
                    'stage'            => $delivery->stage,
                    'product_mrp'      => $productPrice,
                    'dsp_commission'   => round($productPrice * 0.05, 2),
                    'commission_status'=> $delivery->dsp_commission_status,
                    'created_at'       => $delivery->created_at->toIso8601String(),
                ];
            });

        $recentTransactions = $dsp->walletTransactions()
            ->take(5)
            ->get()
            ->map(fn($tx) => [
                'id'               => $tx->id,
                'amount'           => (float)$tx->amount,
                'type'             => $tx->type,
                'source'           => $tx->source,
                'description'      => $tx->description,
                'balance_after'    => (float)$tx->balance_after,
                'created_at'       => $tx->created_at->toIso8601String(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'dsp' => [
                    'id'                 => $dsp->id,
                    'business_name'      => $dsp->business_name ?: $dsp->applicant_name,
                    'applicant_name'     => $dsp->applicant_name,
                    'mobile'             => $dsp->mobile,
                    'territory_area'     => $dsp->preferred_territory_area,
                    'district'           => $dsp->district,
                    'state'              => $dsp->state,
                    'serviced_pincodes'  => $servicedPincodes,
                ],
                'stats' => [
                    'total_deliveries'   => $totalDeliveries,
                    'pending_deliveries' => $pendingDeliveries,
                    'out_for_delivery'   => $outForDeliveryCount,
                    'delivered'          => $deliveredCount,
                    'commission_rate'    => '5.0%',
                ],
                'wallet' => [
                    'available_balance'  => (float)$dsp->wallet_balance,
                    'total_earned'       => (float)$dsp->total_earned,
                    'total_redeemed'     => (float)$dsp->total_redeemed,
                ],
                'recent_deliveries'      => $recentDeliveries,
                'recent_transactions'    => $recentTransactions,
            ],
        ]);
    }

    /**
     * GET /api/dsp/deliveries
     * List deliveries in DSP territory with status filtering and pagination
     */
    public function deliveries(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $status = $request->query('status', $request->input('status', 'all'));
        $search = $request->query('search', $request->input('search'));
        $perPage = (int)($request->query('per_page', $request->input('per_page', 15)));

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

        $deliveries = $query->orderBy('id', 'desc')->paginate($perPage);

        $formatted = $deliveries->getCollection()->map(function ($delivery) {
            $booking = $delivery->booking;
            $order = $delivery->order;
            $productPrice = (float)($booking?->mrp ?? $order?->total_amount ?? 0);
            $commission = round($productPrice * 0.05, 2);

            return [
                'id'                 => $delivery->id,
                'tracking_number'    => $delivery->tracking_number,
                'order_number'       => $booking?->booking_number ?? $order?->order_number,
                'stage'              => $delivery->stage,
                'product'            => [
                    'name'           => $booking?->product_name ?? 'NEXVIA Product',
                    'model_code'     => $booking?->model_code,
                    'selected_color' => $booking?->selected_color,
                    'quantity'       => $booking?->quantity ?? 1,
                    'mrp'            => $productPrice,
                ],
                'customer'           => [
                    'name'           => $booking?->customer_name ?? $order?->customer_name,
                    'phone'          => $booking?->customer_phone ?? $order?->customer_phone,
                    'email'          => $booking?->customer_email ?? $order?->customer_email,
                ],
                'shipping_address'   => [
                    'address'        => $booking?->shipping_address ?? $order?->shipping_address,
                    'city'           => $booking?->city ?? $order?->city,
                    'state'          => $booking?->state ?? $order?->state,
                    'pincode'        => $booking?->pincode ?? $order?->pincode,
                ],
                'commission'         => [
                    'rate'           => '5.0%',
                    'amount'         => $commission,
                    'status'         => $delivery->dsp_commission_status,
                ],
                'dispatched_at'      => $delivery->dispatched_at?->toIso8601String(),
                'delivered_at'       => $delivery->delivered_at?->toIso8601String(),
                'created_at'         => $delivery->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status'       => true,
            'current_page' => $deliveries->currentPage(),
            'total'        => $deliveries->total(),
            'per_page'     => $deliveries->perPage(),
            'last_page'    => $deliveries->lastPage(),
            'deliveries'   => $formatted,
        ]);
    }

    /**
     * GET /api/dsp/deliveries/{id}
     * Single delivery details
     */
    public function deliveryDetail(Request $request, $id)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

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
            ->first();

        if (!$delivery) {
            return response()->json(['status' => false, 'message' => 'Delivery not found or not in your territory.'], 404);
        }

        $booking = $delivery->booking;
        $order = $delivery->order;
        $productPrice = (float)($booking?->mrp ?? $order?->total_amount ?? 0);
        $commission = round($productPrice * 0.05, 2);

        return response()->json([
            'status' => true,
            'delivery' => [
                'id'                 => $delivery->id,
                'tracking_number'    => $delivery->tracking_number,
                'order_number'       => $booking?->booking_number ?? $order?->order_number,
                'stage'              => $delivery->stage,
                'product'            => [
                    'name'           => $booking?->product_name ?? 'NEXVIA Product',
                    'model_code'     => $booking?->model_code,
                    'selected_color' => $booking?->selected_color,
                    'quantity'       => $booking?->quantity ?? 1,
                    'mrp'            => $productPrice,
                ],
                'customer'           => [
                    'name'           => $booking?->customer_name ?? $order?->customer_name,
                    'phone'          => $booking?->customer_phone ?? $order?->customer_phone,
                    'email'          => $booking?->customer_email ?? $order?->customer_email,
                ],
                'shipping_address'   => [
                    'address'        => $booking?->shipping_address ?? $order?->shipping_address,
                    'city'           => $booking?->city ?? $order?->city,
                    'state'          => $booking?->state ?? $order?->state,
                    'pincode'        => $booking?->pincode ?? $order?->pincode,
                ],
                'commission'         => [
                    'rate'           => '5.0%',
                    'amount'         => $commission,
                    'status'         => $delivery->dsp_commission_status,
                ],
                'delivery_otp'       => $delivery->delivery_otp,
                'delivery_notes'     => $delivery->delivery_notes,
                'dispatched_at'      => $delivery->dispatched_at?->toIso8601String(),
                'delivered_at'       => $delivery->delivered_at?->toIso8601String(),
                'created_at'         => $delivery->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/dsp/deliveries/{id}/status
     * Update delivery status (e.g. Out for Delivery, or Delivered) and credit 5% commission
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $stage = $request->input('stage') ?? $request->input('status') ?? ($bodyJson['stage'] ?? null) ?? ($bodyJson['status'] ?? null);
        $notes = $request->input('delivery_notes') ?? $request->input('notes') ?? ($bodyJson['delivery_notes'] ?? null) ?? ($bodyJson['notes'] ?? null);
        $otp = $request->input('delivery_otp') ?? $request->input('otp') ?? ($bodyJson['delivery_otp'] ?? null) ?? ($bodyJson['otp'] ?? null);

        if (!in_array($stage, ['out_for_delivery', 'delivered'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid stage/status. Allowed: out_for_delivery, delivered.',
            ], 422);
        }

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
            ->first();

        if (!$delivery) {
            return response()->json(['status' => false, 'message' => 'Delivery not found in your territory.'], 404);
        }

        // Assign to this DSP if unassigned
        if (empty($delivery->dsp_id)) {
            $delivery->dsp_id = $dsp->id;
        }

        if (!empty($notes)) {
            $delivery->delivery_notes = $notes;
        }
        if (!empty($otp)) {
            $delivery->delivery_otp = $otp;
        }

        if ($stage === 'out_for_delivery') {
            $delivery->stage = 'out_for_delivery';
            $delivery->dispatched_at = $delivery->dispatched_at ?: now();
            $delivery->save();

            if ($delivery->booking) {
                $delivery->booking->dsp_id = $dsp->id;
                $delivery->booking->booking_status = 'out_for_delivery';
                $delivery->booking->save();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Delivery marked as OUT FOR DELIVERY.',
                'stage'   => 'out_for_delivery',
            ]);
        }

        if ($stage === 'delivered') {
            if ($delivery->stage === 'delivered' && $delivery->dsp_commission_status === 'credited') {
                return response()->json([
                    'status'  => true,
                    'message' => 'Order is already DELIVERED and 5% commission has been credited.',
                    'wallet_balance' => (float)$dsp->wallet_balance,
                ]);
            }

            $delivery->stage = 'delivered';
            $delivery->delivered_at = now();
            $delivery->save();

            if ($delivery->booking) {
                $delivery->booking->dsp_id = $dsp->id;
                $delivery->booking->booking_status = 'completed';
                $delivery->booking->save();
            }

            // Calculate and credit 5% Commission
            $productAmount = (float)($delivery->booking?->mrp ?? $delivery->order?->total_amount ?? 0.00);
            $commissionTx = $dsp->creditDeliveryCommission($productAmount, $delivery, $delivery->booking);

            return response()->json([
                'status'              => true,
                'message'             => 'Delivery completed successfully! 5% Commission credited to your cash wallet.',
                'commission_credited' => (float)$commissionTx->amount,
                'wallet_balance'      => (float)$dsp->fresh()->wallet_balance,
                'transaction_ref'     => $commissionTx->reference_number,
            ]);
        }

        return response()->json(['status' => false, 'message' => 'No action performed.'], 400);
    }

    /**
     * GET /api/dsp/wallet
     * DSP Wallet Balance & Transactions Statement
     */
    public function wallet(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $perPage = (int)($request->query('per_page', $request->input('per_page', 20)));
        $transactions = $dsp->walletTransactions()->paginate($perPage);

        $formatted = $transactions->getCollection()->map(fn($tx) => [
            'id'               => $tx->id,
            'amount'           => (float)$tx->amount,
            'type'             => $tx->type,
            'source'           => $tx->source,
            'reference_number' => $tx->reference_number,
            'description'      => $tx->description,
            'balance_after'    => (float)$tx->balance_after,
            'created_at'       => $tx->created_at->toIso8601String(),
        ]);

        return response()->json([
            'status' => true,
            'wallet' => [
                'wallet_balance' => (float)$dsp->wallet_balance,
                'total_earned'   => (float)$dsp->total_earned,
                'total_redeemed' => (float)$dsp->total_redeemed,
            ],
            'current_page'  => $transactions->currentPage(),
            'total'         => $transactions->total(),
            'transactions'  => $formatted,
        ]);
    }

    /**
     * POST /api/dsp/wallet/redeem
     * Submit Cash Redemption Request to Bank or UPI
     */
    public function requestPayout(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $bodyJson = json_decode($request->getContent(), true) ?? [];

        $amount = (float)($request->input('amount') ?? ($bodyJson['amount'] ?? 0));
        $payoutMode = $request->input('payout_mode') ?? ($bodyJson['payout_mode'] ?? 'bank');

        if ($amount < 100) {
            return response()->json(['status' => false, 'message' => 'Minimum cash redemption amount is ₹100.'], 422);
        }

        if ($amount > (float)$dsp->wallet_balance) {
            return response()->json([
                'status'  => false,
                'message' => 'Requested amount (₹' . number_format($amount, 2) . ') exceeds available balance (₹' . number_format($dsp->wallet_balance, 2) . ').',
            ], 422);
        }

        $bankAccount = $request->input('bank_account_number') ?? ($bodyJson['bank_account_number'] ?? $dsp->payout_account_number);
        $bankIfsc    = $request->input('bank_ifsc') ?? ($bodyJson['bank_ifsc'] ?? $dsp->payout_ifsc);
        $bankName    = $request->input('bank_name') ?? ($bodyJson['bank_name'] ?? $dsp->payout_bank_name);
        $bankHolder  = $request->input('bank_holder_name') ?? ($bodyJson['bank_holder_name'] ?? ($dsp->payout_holder_name ?: ($dsp->applicant_name ?: $dsp->business_name)));
        $upiId       = $request->input('upi_id') ?? ($bodyJson['upi_id'] ?? $dsp->payout_upi_id);

        if ($payoutMode === 'bank' && empty($bankAccount)) {
            return response()->json(['status' => false, 'message' => 'Bank account number is required for bank transfer.'], 422);
        }
        if ($payoutMode === 'upi' && empty($upiId)) {
            return response()->json(['status' => false, 'message' => 'UPI ID is required for UPI payout.'], 422);
        }

        // Deduct from wallet balance
        $newBalance = round((float)$dsp->wallet_balance - $amount, 2);
        $newRedeemed = round((float)$dsp->total_redeemed + $amount, 2);

        $dsp->update([
            'wallet_balance'        => $newBalance,
            'total_redeemed'        => $newRedeemed,
            'payout_account_number' => $bankAccount,
            'payout_ifsc'           => $bankIfsc ? strtoupper($bankIfsc) : null,
            'payout_bank_name'      => $bankName,
            'payout_holder_name'    => $bankHolder,
            'payout_upi_id'         => $upiId,
        ]);

        $requestNumber = 'DSP-PAY-' . date('Ymd') . '-' . rand(1000, 9999);

        $payout = DspPayoutRequest::create([
            'dsp_id'              => $dsp->id,
            'request_number'      => $requestNumber,
            'amount'              => $amount,
            'payout_mode'         => $payoutMode,
            'bank_account_number' => $bankAccount,
            'bank_ifsc'           => $bankIfsc ? strtoupper($bankIfsc) : null,
            'bank_name'           => $bankName,
            'bank_holder_name'    => $bankHolder,
            'upi_id'              => $upiId,
            'status'              => 'pending',
        ]);

        DspWalletTransaction::create([
            'dsp_id'           => $dsp->id,
            'amount'           => $amount,
            'type'             => 'debit',
            'source'           => 'cash_redemption',
            'reference_number' => $requestNumber,
            'description'      => "Cash redemption payout request #{$requestNumber} submitted to " . ($payoutMode === 'upi' ? "UPI: {$upiId}" : "Bank A/c: {$bankAccount}"),
            'balance_after'    => $newBalance,
        ]);

        return response()->json([
            'status'         => true,
            'message'        => "Cash redemption request of ₹" . number_format($amount, 2) . " submitted successfully.",
            'request_number' => $requestNumber,
            'amount'         => $amount,
            'payout_mode'    => $payoutMode,
            'wallet_balance' => $newBalance,
        ], 201);
    }

    /**
     * GET /api/dsp/wallet/payout-requests
     * List DSP cash redemption requests
     */
    public function payoutRequests(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $requests = $dsp->payoutRequests()->paginate(15);

        return response()->json([
            'status'          => true,
            'payout_requests' => $requests,
        ]);
    }

    /**
     * GET /api/dsp/profile
     * DSP Profile and territory info
     */
    public function profile(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        return response()->json([
            'status' => true,
            'dsp'    => [
                'id'                     => $dsp->id,
                'application_number'     => $dsp->application_number,
                'business_name'          => $dsp->business_name ?: $dsp->applicant_name,
                'applicant_name'         => $dsp->applicant_name,
                'mobile'                 => $dsp->mobile,
                'email'                  => $dsp->email,
                'district'               => $dsp->district,
                'state'                  => $dsp->state,
                'territory_area'         => $dsp->preferred_territory_area,
                'serviced_pincodes'      => $dsp->servicedPincodesArray(),
                'premises_address'       => $dsp->complete_address,
                'premises_pincode'       => $dsp->premises_pincode,
                'technicians_count'      => (int)$dsp->technicians_count,
                'vehicles_count'         => $dsp->total_vehicles,
                'payout_settings'        => [
                    'account_number'     => $dsp->payout_account_number,
                    'ifsc'               => $dsp->payout_ifsc,
                    'bank_name'          => $dsp->payout_bank_name,
                    'holder_name'        => $dsp->payout_holder_name,
                    'upi_id'             => $dsp->payout_upi_id,
                ],
                'wallet'                 => [
                    'balance'            => (float)$dsp->wallet_balance,
                    'total_earned'       => (float)$dsp->total_earned,
                    'total_redeemed'     => (float)$dsp->total_redeemed,
                ],
            ],
        ]);
    }

    /**
     * POST /api/dsp/profile
     * Update DSP default payout credentials or password
     */
    public function updateProfile(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP.'], 401);
        }

        $bodyJson = json_decode($request->getContent(), true) ?? [];

        $payoutAccount = $request->input('payout_account_number') ?? ($bodyJson['payout_account_number'] ?? null);
        $payoutIfsc    = $request->input('payout_ifsc') ?? ($bodyJson['payout_ifsc'] ?? null);
        $payoutBank    = $request->input('payout_bank_name') ?? ($bodyJson['payout_bank_name'] ?? null);
        $payoutHolder  = $request->input('payout_holder_name') ?? ($bodyJson['payout_holder_name'] ?? null);
        $payoutUpi     = $request->input('payout_upi_id') ?? ($bodyJson['payout_upi_id'] ?? null);
        $newPassword   = $request->input('new_password') ?? ($bodyJson['new_password'] ?? null);

        if (!empty($payoutAccount)) { $dsp->payout_account_number = $payoutAccount; }
        if (!empty($payoutIfsc))    { $dsp->payout_ifsc = strtoupper($payoutIfsc); }
        if (!empty($payoutBank))    { $dsp->payout_bank_name = $payoutBank; }
        if (!empty($payoutHolder))  { $dsp->payout_holder_name = $payoutHolder; }
        if (!empty($payoutUpi))     { $dsp->payout_upi_id = $payoutUpi; }

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                return response()->json(['status' => false, 'message' => 'New password must be at least 6 characters.'], 422);
            }
            $dsp->password = Hash::make($newPassword);
        }

        $dsp->save();

        return response()->json([
            'status'  => true,
            'message' => 'DSP Profile and payout settings updated successfully.',
            'dsp'     => [
                'id'              => $dsp->id,
                'payout_settings' => [
                    'account_number' => $dsp->payout_account_number,
                    'ifsc'           => $dsp->payout_ifsc,
                    'bank_name'      => $dsp->payout_bank_name,
                    'holder_name'    => $dsp->payout_holder_name,
                    'upi_id'         => $dsp->payout_upi_id,
                ],
            ],
        ]);
    }

    /**
     * Resolve customer strictly from authorization token or middleware
     */
    protected function resolveCustomer(Request $request): ?Customer
    {
        $customer = $request->attributes->get('authenticated_customer') ?? $request->user();
        if ($customer instanceof Customer) {
            return $customer;
        }

        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $token = $request->bearerToken()
            ?? $request->input('api_token')
            ?? $request->input('token')
            ?? $request->header('api_token')
            ?? $request->header('token')
            ?? ($bodyJson['api_token'] ?? null)
            ?? ($bodyJson['token'] ?? null);

        if (!empty($token)) {
            $found = Customer::where('api_token', $token)->first();
            if ($found) return $found;
        }

        return null;
    }

    /**
     * GET or POST /api/dsp/nearby or /api/customer/dsp/nearby
     * Load all nearby authorised DSP delivery partners based on pincode OR user address.
     * Allows customer to view, select, and assign the delivery partner.
     */
    public function nearby(Request $request)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];

        // 1. Direct input parameters
        $pincode  = $request->query('pincode', $request->input('pincode', $bodyJson['pincode'] ?? null));
        $address  = $request->query('address', $request->input('address', $bodyJson['address'] ?? null));
        $district = $request->query('district', $request->input('district', $bodyJson['district'] ?? ($bodyJson['city'] ?? $request->input('city'))));
        $state    = $request->query('state', $request->input('state', $bodyJson['state'] ?? null));
        $source   = 'query_parameters';

        // 2. If pincode is not explicitly provided, extract 6-digit PIN from address string
        if (empty($pincode) && !empty($address)) {
            if (preg_match('/\b[1-9][0-9]{5}\b/', (string)$address, $matches)) {
                $pincode = $matches[0];
                $source = 'extracted_from_address';
            }
        }

        // 3. If still empty, resolve logged-in customer's saved address or profile
        if (empty($pincode) && empty($address)) {
            $customer = $this->resolveCustomer($request);
            if ($customer) {
                $defaultAddr = UserAddress::where('user_id', $customer->id)
                    ->orderByDesc('is_default')
                    ->first();

                if ($defaultAddr) {
                    $pincode  = $defaultAddr->pincode;
                    $address  = $defaultAddr->address;
                    $district = $district ?: $defaultAddr->city;
                    $state    = $state ?: $defaultAddr->state;
                    $source   = 'customer_saved_address';
                } elseif (!empty($customer->pincode) || !empty($customer->address)) {
                    $pincode  = $customer->pincode;
                    $address  = $customer->address;
                    $district = $district ?: $customer->city;
                    $state    = $state ?: $customer->state;
                    $source   = 'customer_profile';
                }
            }
        }

        // 4. Find matching DSPs via matching service
        $matchingService = app(DspMatchingService::class);
        $dsps = $matchingService->findAvailableDsps($pincode, $district, $state);

        $hasDirectMatch = false;
        $formatted = $dsps->values()->map(function ($dsp, $index) use (&$hasDirectMatch) {
            $isDirect = ($dsp->match_type ?? '') === 'exact_pincode';
            if ($isDirect) {
                $hasDirectMatch = true;
            }

            return [
                'id'                 => $dsp->id,
                'business_name'      => $dsp->business_name ?: $dsp->applicant_name,
                'applicant_name'     => $dsp->applicant_name,
                'mobile'             => $dsp->mobile,
                'email'              => $dsp->email,
                'territory_area'     => $dsp->preferred_territory_area ?: ($dsp->district . ', ' . $dsp->state),
                'district'           => $dsp->district,
                'state'              => $dsp->state,
                'premises_address'   => $dsp->complete_address,
                'premises_pincode'   => $dsp->premises_pincode,
                'serviced_pincodes'  => $dsp->servicedPincodesArray(),
                'match_type'         => $dsp->match_type ?? 'regional',
                'match_label'        => $dsp->match_label ?? 'Delivery Partner',
                'is_direct_match'    => $isDirect,
                'is_recommended'     => ($index === 0),
                'technicians_count'  => (int)$dsp->technicians_count,
                'vehicles_count'     => $dsp->total_vehicles,
                'commission_rate'    => '5.0%',
            ];
        });

        return response()->json([
            'status' => true,
            'search_criteria' => [
                'pincode'          => $pincode,
                'district'         => $district,
                'state'            => $state,
                'address_queried'  => $address,
                'detection_source' => $source,
            ],
            'count'                      => $formatted->count(),
            'has_direct_pincode_partner' => $hasDirectMatch,
            'recommended_dsp'            => $formatted->first(),
            'dsps'                       => $formatted,
            'nearby_dsps'                => $formatted,
        ]);
    }

    /**
     * GET /api/dsp/available-by-pincode
     * Public API returning matching DSP partners for customer delivery pincode (alias for nearby)
     */
    public function availableByPincode(Request $request)
    {
        return $this->nearby($request);
    }

    /**
     * POST /api/dsp/apply
     * Submit a DSP application via REST API
     */
    public function store(Request $request)
    {
        $request->validate([
            'applicant_name'       => 'required|string|max:255',
            'mobile'               => 'required|string|max:20',
            'business_name'        => 'required|string|max:255',
            'declaration_agreed'   => 'required',
            'deposit_payment_proof'=> 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'signature_file'       => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $proofPath = null;
        if ($request->hasFile('deposit_payment_proof')) {
            $file = $request->file('deposit_payment_proof');
            $fileName = 'dsp_proof_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/dsp'), $fileName);
            $proofPath = 'uploads/dsp/' . $fileName;
        }

        $sigPath = null;
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $fileName = 'dsp_sig_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/dsp'), $fileName);
            $sigPath = 'uploads/dsp/' . $fileName;
        }

        $appNumber = 'DSP-' . date('Y') . '-' . strtoupper(Str::random(6));

        $application = DspApplication::create([
            'application_number'               => $appNumber,
            'application_date'                 => now()->toDateString(),
            'preferred_territory_area'         => $request->preferred_territory_area,
            'pincodes'                         => $request->pincodes,
            'district'                         => $request->district,
            'state'                            => $request->state,
            'applicant_name'                   => $request->applicant_name,
            'father_or_spouse_name'            => $request->father_or_spouse_name,
            'date_of_birth'                    => $request->date_of_birth,
            'mobile'                           => $request->mobile,
            'whatsapp'                         => $request->whatsapp,
            'email'                            => $request->email,
            'residential_address'              => $request->residential_address,
            'residential_pincode'              => $request->residential_pincode,
            'business_name'                    => $request->business_name,
            'business_constitution'            => $request->business_constitution ?? 'proprietorship',
            'business_constitution_other'      => $request->business_constitution_other,
            'year_established'                 => $request->year_established,
            'pan'                              => $request->pan ? strtoupper($request->pan) : null,
            'gstin'                            => $request->gstin ? strtoupper($request->gstin) : null,
            'existing_business_activity'       => $request->existing_business_activity,
            'years_of_experience'              => $request->years_of_experience,
            'premises_type'                    => $request->premises_type ?? 'owned',
            'complete_address'                 => $request->complete_address,
            'premises_pincode'                 => $request->premises_pincode,
            'total_area_sqft'                  => $request->total_area_sqft,
            'frontage_feet'                    => $request->frontage_feet,
            'available_facilities'             => is_array($request->available_facilities) ? $request->available_facilities : json_decode($request->available_facilities, true) ?? [],
            'presently_operate_service_centre' => filter_var($request->presently_operate_service_centre, FILTER_VALIDATE_BOOLEAN),
            'technicians_count'                => (int)$request->technicians_count,
            'ev_technician_status'             => $request->ev_technician_status ?? 'no',
            'electrical_technician_status'     => $request->electrical_technician_status ?? 'no',
            'home_appliance_technician_status' => $request->home_appliance_technician_status ?? 'no',
            'agree_to_nexvia_training'         => filter_var($request->agree_to_nexvia_training, FILTER_VALIDATE_BOOLEAN),
            'products_handled'                 => is_array($request->products_handled) ? $request->products_handled : json_decode($request->products_handled, true) ?? [],
            'vehicles_two_wheeler'             => (int)$request->vehicles_two_wheeler,
            'vehicles_three_wheeler'           => (int)$request->vehicles_three_wheeler,
            'vehicles_pickup_lcv'              => (int)$request->vehicles_pickup_lcv,
            'vehicles_other'                   => $request->vehicles_other,
            'max_delivery_radius_km'           => $request->max_delivery_radius_km,
            'pdi_handover_sop'                 => filter_var($request->pdi_handover_sop, FILTER_VALIDATE_BOOLEAN),
            'otp_delivery_confirmation'        => filter_var($request->otp_delivery_confirmation, FILTER_VALIDATE_BOOLEAN),
            'security_deposit_amount'          => 1000000.00,
            'deposit_terms_agreed'             => true,
            'deposit_payment_status'           => 'pending',
            'deposit_transaction_reference'    => $request->deposit_transaction_reference,
            'deposit_payment_proof'            => $proofPath,
            'declaration_agreed'               => true,
            'declaration_date'                 => now()->toDateString(),
            'declaration_signature_name'       => $request->declaration_signature_name ?? $request->applicant_name,
            'signature_file'                   => $sigPath,
            'status'                           => 'pending',
        ]);

        // Send acknowledgement email to applicant & notification to admin via SMTP
        if (!empty($application->email)) {
            try {
                $appName = config('mail.from.name', 'NEXVIA');
                Mail::raw("Dear {$application->applicant_name},\n\nThank you for submitting your application to become an Authorised Delivery & Service Partner (DSP) with {$appName}.\n\nApplication Number: {$application->application_number}\nBusiness Name: {$application->business_name}\nTerritory: {$application->preferred_territory_area} ({$application->district}, {$application->state})\n\nOur team is reviewing your application and documentation. You will receive updates shortly.\n\nBest regards,\n{$appName} Partner Operations", function ($m) use ($application, $appName) {
                    $m->to($application->email)
                      ->subject("{$appName} DSP Application Received - {$application->application_number}");
                });
            } catch (\Throwable $e) {
                \Log::warning("DSP applicant email failed: " . $e->getMessage());
            }
        }

        // Notify company admin inbox
        try {
            $adminEmail = config('mail.from.address', 'nexviadls@gmail.com');
            $appName = config('mail.from.name', 'NEXVIA');
            Mail::raw("New DSP Application Received!\n\nApplication Number: {$application->application_number}\nApplicant: {$application->applicant_name}\nBusiness: {$application->business_name}\nMobile: {$application->mobile}\nEmail: " . ($application->email ?: 'N/A') . "\nDistrict: {$application->district}, {$application->state}\nTerritory: {$application->preferred_territory_area}\n\nPlease review this application in the admin portal.", function ($m) use ($adminEmail, $application, $appName) {
                $m->to($adminEmail)
                  ->subject("New DSP Partner Application - {$application->application_number} ({$application->applicant_name})");
            });
        } catch (\Throwable $e) {
            \Log::warning("Admin DSP notification email failed: " . $e->getMessage());
        }

        return response()->json([
            'status'             => true,
            'message'            => 'DSP Partner application submitted successfully!',
            'application_number' => $application->application_number,
            'data'               => $application,
        ], 201);
    }

    /**
     * GET /api/dsp/track/{applicationNumber}
     * Track status of a DSP application
     */
    public function track($applicationNumber)
    {
        $app = DspApplication::where('application_number', $applicationNumber)->first();

        if (!$app) {
            return response()->json([
                'status'  => false,
                'message' => 'Application not found with number ' . $applicationNumber,
            ], 404);
        }

        return response()->json([
            'status'             => true,
            'application_number' => $app->application_number,
            'applicant_name'     => $app->applicant_name,
            'business_name'      => $app->business_name,
            'status'             => $app->status,
            'applied_at'         => $app->created_at->toIso8601String(),
        ]);
    }

    /**
     * GET /api/dsp/service-requests
     * List all service / problem requests allocated to this DSP with attendance & resolution tracking.
     */
    public function serviceRequests(Request $request)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP Partner.'], 401);
        }

        $query = ServiceRequest::where('dsp_id', $dsp->id)->with(['user', 'booking.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_attended')) {
            $isAttended = filter_var($request->is_attended, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_attended', $isAttended);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('pincode', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $tickets = $query->orderBy('id', 'desc')->paginate($perPage);

        $stats = [
            'total_assigned'    => ServiceRequest::where('dsp_id', $dsp->id)->count(),
            'attended_count'    => ServiceRequest::where('dsp_id', $dsp->id)->where('is_attended', true)->count(),
            'pending_attention' => ServiceRequest::where('dsp_id', $dsp->id)->where('is_attended', false)->where('status', '!=', 'resolved')->count(),
            'resolved_count'    => ServiceRequest::where('dsp_id', $dsp->id)->where('status', 'resolved')->count(),
        ];

        return response()->json([
            'status' => true,
            'stats'  => $stats,
            'data'   => $tickets->items(),
            'meta'   => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'total'        => $tickets->total(),
            ],
        ]);
    }

    /**
     * GET /api/dsp/service-requests/{id}
     * Get details of a single service ticket assigned to this DSP.
     */
    public function serviceRequestDetail(Request $request, $id)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP Partner.'], 401);
        }

        $ticket = ServiceRequest::where('dsp_id', $dsp->id)
            ->with(['user', 'booking.product'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id);
                } else {
                    $q->where('ticket_number', $id);
                }
            })
            ->first();

        if (!$ticket) {
            return response()->json([
                'status'  => false,
                'message' => 'Service request not found or not allocated to your DSP account.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $ticket,
        ]);
    }

    /**
     * POST /api/dsp/service-requests/{id}/status
     * DSP updates problem attendance and resolution status.
     */
    public function updateServiceRequestStatus(Request $request, $id)
    {
        $dsp = $this->resolveDsp($request);
        if (!$dsp) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated DSP Partner.'], 401);
        }

        $ticket = ServiceRequest::where('dsp_id', $dsp->id)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id);
                } else {
                    $q->where('ticket_number', $id);
                }
            })
            ->first();

        if (!$ticket) {
            return response()->json([
                'status'  => false,
                'message' => 'Service request not found or not allocated to your account.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status'            => 'nullable|string|in:open,attended,in_progress,resolved,cancelled',
            'is_attended'       => 'nullable|boolean',
            'attended_by_name'  => 'nullable|string|max:255',
            'attended_by_phone' => 'nullable|string|max:20',
            'dsp_notes'         => 'nullable|string|max:2000',
            'resolution_notes'  => 'nullable|string|max:2000',
            'resolution_proof'  => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $newStatus = $request->input('status', $ticket->status);
        $ticket->status = $newStatus;

        // If explicitly set, or status indicates attention
        if ($request->has('is_attended')) {
            $ticket->is_attended = filter_var($request->is_attended, FILTER_VALIDATE_BOOLEAN);
            if ($ticket->is_attended && !$ticket->attended_at) {
                $ticket->attended_at = now();
            }
        } elseif (in_array($newStatus, ['attended', 'in_progress', 'resolved'])) {
            $ticket->is_attended = true;
            if (!$ticket->attended_at) {
                $ticket->attended_at = now();
            }
        }

        if ($request->filled('attended_by_name')) {
            $ticket->attended_by_name = $request->attended_by_name;
        }
        if ($request->filled('attended_by_phone')) {
            $ticket->attended_by_phone = $request->attended_by_phone;
        }
        if ($request->filled('dsp_notes')) {
            $ticket->dsp_notes = $request->dsp_notes;
        }

        // If marked resolved
        if ($newStatus === 'resolved') {
            $ticket->resolved_at = now();
            if ($request->filled('resolution_notes')) {
                $ticket->resolution_notes = $request->resolution_notes;
            }

            if ($request->hasFile('resolution_proof')) {
                $destination = public_path('uploads/service_attachments');
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }
                $f = $request->file('resolution_proof');
                $name = 'res_proof_' . $ticket->ticket_number . '_' . time() . '.' . $f->getClientOriginalExtension();
                $f->move($destination, $name);
                $ticket->resolution_proof = 'uploads/service_attachments/' . $name;
            }
        }

        $ticket->save();

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => "Service ticket #{$ticket->ticket_number} updated successfully.",
            'data'    => [
                'id'            => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status'        => $ticket->status,
                'attendance'    => [
                    'is_attended'       => (bool)$ticket->is_attended,
                    'attended_at'       => $ticket->attended_at?->toIso8601String(),
                    'attended_by_name'  => $ticket->attended_by_name,
                    'attended_by_phone' => $ticket->attended_by_phone,
                ],
                'dsp_notes'        => $ticket->dsp_notes,
                'resolved_at'      => $ticket->resolved_at?->toIso8601String(),
                'resolution_notes' => $ticket->resolution_notes,
                'resolution_proof' => $ticket->resolution_proof,
            ],
        ]);
    }
}
