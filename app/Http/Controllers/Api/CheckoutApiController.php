<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CartItem;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;

class CheckoutApiController extends Controller
{
    /**
     * Resolve the current customer from token, middleware attributes, or request parameter.
     */
    protected function resolveCustomer(Request $request)
    {
        $customer = $request->attributes->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $userId = $request->input('user_id')
                ?? $request->input('userId')
                ?? $request->input('customer_id')
                ?? $request->input('customerId')
                ?? ($bodyJson['user_id'] ?? null)
                ?? ($bodyJson['userId'] ?? null)
                ?? ($bodyJson['customer_id'] ?? null)
                ?? ($bodyJson['customerId'] ?? null);

            if (!empty($userId)) {
                $customer = Customer::find($userId);
            }
        }

        return $customer;
    }

    /**
     * POST /api/checkout/calculate
     * Compute token amount (20%), balance (80%), taxes & delivery for:
     * - Single item: { productId, selectedColor, quantity }
     * - Multi-items: { items: [ { productId, selectedColor, quantity } ] }
     * - Or automatically from customer's active cart if no items/productId passed.
     */
    public function calculate(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $productId = $request->input('productId')
                ?? $request->input('product_id')
                ?? ($bodyJson['productId'] ?? null)
                ?? ($bodyJson['product_id'] ?? null);

            $selectedColor = $request->input('selectedColor')
                ?? $request->input('selected_color')
                ?? $request->input('color')
                ?? ($bodyJson['selectedColor'] ?? null)
                ?? ($bodyJson['selected_color'] ?? null)
                ?? ($bodyJson['color'] ?? null);

            $quantity = (int) (
                $request->input('quantity')
                ?? ($bodyJson['quantity'] ?? 1)
            );
            if ($quantity < 1) {
                $quantity = 1;
            }

            $itemsToCompute = [];

            // Case 1: Direct single item payload { productId, selectedColor, quantity }
            if (!empty($productId)) {
                $itemsToCompute[] = [
                    'product_id'     => $productId,
                    'selected_color' => $selectedColor,
                    'quantity'       => $quantity,
                ];
            }
            // Case 2: Array of items { items: [...] }
            elseif (!empty($request->input('items')) || !empty($bodyJson['items'])) {
                $rawItems = $request->input('items') ?? ($bodyJson['items'] ?? []);
                foreach ($rawItems as $raw) {
                    $pId = $raw['productId'] ?? ($raw['product_id'] ?? null);
                    if ($pId) {
                        $itemsToCompute[] = [
                            'product_id'     => $pId,
                            'selected_color' => $raw['selectedColor'] ?? ($raw['selected_color'] ?? ($raw['color'] ?? null)),
                            'quantity'       => max(1, (int) ($raw['quantity'] ?? 1)),
                        ];
                    }
                }
            }
            // Case 3: Calculate from customer's active Cart
            else {
                $customer = $this->resolveCustomer($request);
                if ($customer) {
                    $cartItems = CartItem::where('user_id', $customer->id)->get();
                    foreach ($cartItems as $ci) {
                        $itemsToCompute[] = [
                            'product_id'     => $ci->product_id,
                            'selected_color' => $ci->selected_color,
                            'quantity'       => $ci->quantity,
                        ];
                    }
                }
            }

            if (empty($itemsToCompute)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No product specified for calculation. Provide { productId, selectedColor, quantity } or user_id.',
                ], 422);
            }

            // Calculate each item and accumulators
            $computedItems = [];
            $totalQuantity = 0;
            $subtotalMrp   = 0.0;
            $totalToken    = 0.0;
            $totalBalance  = 0.0;

            foreach ($itemsToCompute as $itemData) {
                $product = is_numeric($itemData['product_id'])
                    ? Product::with('category')->find($itemData['product_id'])
                    : Product::with('category')->where('slug', $itemData['product_id'])->orWhere('sku', $itemData['product_id'])->first();

                if (!$product) {
                    continue;
                }

                $qty = max(1, (int) $itemData['quantity']);
                $unitMrp = (float) $product->mrp;
                $itemTotalMrp = $unitMrp * $qty;

                $bookingPct = (float) ($product->booking_percentage ?: 20.00);
                $tokenAmount = round($itemTotalMrp * ($bookingPct / 100), 2);
                $balancePct = 100.00 - $bookingPct;
                $balanceAmount = round($itemTotalMrp - $tokenAmount, 2);

                $taxRate = 18.0;
                $taxableAmount = round($itemTotalMrp / (1 + ($taxRate / 100)), 2);
                $itemTax = round($itemTotalMrp - $taxableAmount, 2);

                $mainImageUrl = ImageHelper::resolve($product->main_image);

                $totalQuantity += $qty;
                $subtotalMrp   += $itemTotalMrp;
                $totalToken    += $tokenAmount;
                $totalBalance  += $balanceAmount;

                $computedItems[] = [
                    'product_id'          => $product->id,
                    'name'                => $product->name,
                    'slug'                => $product->slug,
                    'model_code'          => $product->model_code,
                    'sku'                 => $product->sku,
                    'selected_color'      => $itemData['selected_color'],
                    'quantity'            => $qty,
                    'unit_mrp'            => $unitMrp,
                    'item_total_mrp'      => $itemTotalMrp,
                    'token_percentage'    => $bookingPct,
                    'token_amount'        => $tokenAmount,
                    'balance_percentage'  => $balancePct,
                    'balance_amount'      => $balanceAmount,
                    'main_image'          => $mainImageUrl,
                    'imageUrl'            => $mainImageUrl,
                    'stock'               => (int) $product->stock,
                    'in_stock'            => (int) $product->stock >= $qty,
                ];
            }

            if (empty($computedItems)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product(s) not found for calculation.',
                ], 404);
            }

            // Overall Tax Calculations (GST 18% inclusive)
            $overallTaxRate = 18.0;
            $taxableSubtotal = round($subtotalMrp / (1 + ($overallTaxRate / 100)), 2);
            $totalTaxAmount  = round($subtotalMrp - $taxableSubtotal, 2);
            $cgstAmount      = round($totalTaxAmount / 2, 2);
            $sgstAmount      = round($totalTaxAmount / 2, 2);

            // Free delivery on all EV products & orders above ₹5,000
            $deliveryFee = 0.0;
            $deliveryStatus = 'FREE';

            $grandTotal = round($subtotalMrp + $deliveryFee, 2);

            return response()->json([
                'status'  => true,
                'message' => 'Checkout amounts calculated successfully.',
                'calculation' => [
                    'summary' => [
                        'total_items_count'      => count($computedItems),
                        'total_quantity'         => $totalQuantity,
                        'subtotal_mrp'           => round($subtotalMrp, 2),
                        'token_percentage'       => 20.0,
                        'token_amount'           => round($totalToken, 2),
                        'balance_percentage'     => 80.0,
                        'balance_amount'         => round($totalBalance, 2),
                        'delivery_fee'           => $deliveryFee,
                        'grand_total'            => $grandTotal,
                    ],
                    'taxes' => [
                        'tax_rate_percentage'    => $overallTaxRate,
                        'taxable_amount'         => $taxableSubtotal,
                        'cgst_percentage'        => 9.0,
                        'cgst_amount'            => $cgstAmount,
                        'sgst_percentage'        => 9.0,
                        'sgst_amount'            => $sgstAmount,
                        'total_tax'              => $totalTaxAmount,
                        'tax_status'             => 'Inclusive in MRP',
                        'note'                   => 'All statutory GST (18%) is included within the MRP price.',
                    ],
                    'delivery' => [
                        'charges'                => $deliveryFee,
                        'status'                 => $deliveryStatus,
                        'title'                  => 'Free Doorstep Delivery',
                        'estimated_days'         => '5-7 Business Days',
                        'includes'               => 'Unboxing, inspection, and door delivery included.',
                    ],
                    'payment_schedule' => [
                        'token_20_percent' => [
                            'title'              => 'Token Booking Amount (20%)',
                            'percentage'         => '20%',
                            'amount'             => round($totalToken, 2),
                            'due'                => 'Payable Now to Reserve Vehicle/Product',
                            'timing'             => 'Immediate at Checkout',
                            'is_refundable'      => false,
                        ],
                        'balance_80_percent' => [
                            'title'              => 'Remaining Balance (80%)',
                            'percentage'         => '80%',
                            'amount'             => round($totalBalance, 2),
                            'due'                => 'Payable within 60 days before delivery',
                            'timing'             => 'Flexible within 60 Days',
                            'eligible_for_transfer' => true,
                        ],
                    ],
                    'payable_breakdown' => [
                        'payable_now'            => round($totalToken, 2),
                        'payable_later'          => round($totalBalance, 2),
                        'total_payable'          => $grandTotal,
                    ],
                    'payment_options' => [
                        'online_gateway' => [
                            'name'        => 'Razorpay / Cards / Net Banking',
                            'enabled'     => true,
                        ],
                        'upi_qr' => [
                            'name'         => 'Scan & Pay via UPI QR Code',
                            'enabled'      => true,
                            'account_name' => 'DLS AGRO INFRAVENTURE PRIVATE LIMITED',
                            'upi_id'       => 'dlsagroin.09@idfcbank',
                            'bank_name'    => 'IDFC FIRST Bank',
                            'qr_image_url' => asset('images/dls_payment_qr.png'),
                            'instructions' => 'Scan with any UPI App (GPay, PhonePe, Paytm, BHIM)',
                        ],
                    ],
                    'items' => $computedItems,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to calculate checkout amounts.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
