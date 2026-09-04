<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Installation;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderDeliveryApiController extends Controller
{
    /**
     * POST /api/customer/orders/checkout
     * Multi-item Cart Checkout with Product Credit redemption & 7-stage delivery initiation.
     */
    public function checkout(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'nullable|integer|min:1',
            'shipping_address'        => 'required|string',
            'city'                    => 'required|string',
            'state'                   => 'required|string',
            'pincode'                 => 'required|string',
            'payment_type'            => 'nullable|in:booking_20,full_payment',
            'payment_method'          => 'nullable|in:upi,card,net_banking,emi',
            'use_product_credit'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $paymentType   = $request->input('payment_type', 'booking_20');
            $paymentMethod = $request->input('payment_method', 'upi');
            $useCredit     = (bool)$request->input('use_product_credit', false);

            $totalAmount   = 0.00;
            $bookingAmount = 0.00;
            $orderItemsData = [];

            foreach ($request->items as $itemData) {
                $product  = Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity'] ?? 1;

                $mrp       = $product->mrp * $quantity;
                $pct       = $product->booking_percentage ?: 20;
                $bAmount   = $mrp * ($pct / 100);

                $totalAmount   += $mrp;
                $bookingAmount += $bAmount;

                $orderItemsData[] = [
                    'product_id'           => $product->id,
                    'product_name'         => $product->name,
                    'quantity'             => $quantity,
                    'unit_mrp'             => $product->mrp,
                    'unit_booking_amount'  => $product->mrp * ($pct / 100),
                ];
            }

            $payableAmount = ($paymentType === 'full_payment') ? $totalAmount : $bookingAmount;
            $balanceAmount = $totalAmount - $payableAmount;

            $productCreditApplied = 0.00;
            if ($useCredit) {
                $availableCredit = (float)($user->wallet_balance ?? 0);
                $productCreditApplied = min($availableCredit, $payableAmount);

                if ($productCreditApplied > 0) {
                    $user->wallet_balance = ($user->wallet_balance ?? 0) - $productCreditApplied;
                    $user->save();
                }
            }

            $orderNumber = 'NEX-ORD-' . date('Ymd') . '-' . rand(1000, 9999);

            $order = Order::create([
                'order_number'           => $orderNumber,
                'user_id'                => $user->id,
                'total_amount'           => $totalAmount,
                'booking_amount'         => $bookingAmount,
                'balance_amount'         => $balanceAmount,
                'product_credit_applied' => $productCreditApplied,
                'payment_type'           => $paymentType,
                'payment_method'         => $paymentMethod,
                'payment_status'         => ($paymentType === 'full_payment') ? 'fully_paid' : 'paid',
                'order_status'           => 'confirmed',
                'customer_name'          => $user->name,
                'customer_phone'         => $user->phone,
                'shipping_address'       => $request->shipping_address,
                'city'                   => $request->city,
                'state'                  => $request->state,
                'pincode'                => $request->pincode,
            ]);

            foreach ($orderItemsData as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            // Log wallet redemption if applied
            if ($productCreditApplied > 0) {
                WalletTransaction::create([
                    'user_id'     => $user->id,
                    'amount'      => $productCreditApplied,
                    'type'        => 'debit',
                    'source'      => 'booking_redemption',
                    'description' => "Redeemed NEXVIA Product Credit (₹" . number_format($productCreditApplied, 2) . ") for Order {$order->order_number}",
                ]);
            }

            // Initialize 7-Stage Delivery
            $trackingNumber = 'TRK-' . rand(10000000, 99999999);
            $delivery = Delivery::create([
                'order_id'        => $order->id,
                'tracking_number' => $trackingNumber,
                'stage'           => 'order_confirmed',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully.',
                'data'    => [
                    'order_number'           => $order->order_number,
                    'total_amount'           => (float)$order->total_amount,
                    'booking_amount'         => (float)$order->booking_amount,
                    'balance_amount'         => (float)$order->balance_amount,
                    'product_credit_applied' => (float)$order->product_credit_applied,
                    'payment_type'           => $order->payment_type,
                    'payment_status'         => $order->payment_status,
                    'tracking_number'        => $delivery->tracking_number,
                    'delivery_stage'         => $delivery->stage,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Checkout failed.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/orders/deliveries/{trackingNumber}
     * Track 7-stage order delivery lifecycle.
     */
    public function trackDelivery(Request $request, $trackingNumber)
    {
        $delivery = Delivery::where('tracking_number', $trackingNumber)
            ->with(['order.items', 'booking'])
            ->first();

        if (!$delivery) {
            return response()->json(['status' => false, 'message' => 'Delivery tracking record not found.'], 404);
        }

        $stages = [
            'order_confirmed'        => 'Order Confirmed',
            'processing'             => 'Processing',
            'dispatched'             => 'Dispatched',
            'out_for_delivery'       => 'Out for Delivery',
            'delivered'              => 'Delivered',
            'installation_pending'   => 'Installation Pending',
            'installation_completed' => 'Installation Completed',
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'tracking_number'           => $delivery->tracking_number,
                'current_stage'             => $delivery->stage,
                'current_stage_label'       => $stages[$delivery->stage] ?? ucfirst($delivery->stage),
                'dispatched_at'             => $delivery->dispatched_at ? $delivery->dispatched_at->format('Y-m-d H:i:s') : null,
                'delivered_at'              => $delivery->delivered_at ? $delivery->delivered_at->format('Y-m-d H:i:s') : null,
                'installation_completed_at' => $delivery->installation_completed_at ? $delivery->installation_completed_at->format('Y-m-d H:i:s') : null,
                'stages_flow'               => $stages,
            ],
        ]);
    }
}
