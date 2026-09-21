<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Customer;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    /**
     * Resolve customer strictly from authorization token.
     */
    protected function resolveCustomer(Request $request)
    {
        $customer = $request->attributes->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $customer = Customer::where('api_token', $token)->first();
            }
        }

        return $customer;
    }

    /**
     * Resolve product from request (by ID or slug).
     */
    protected function resolveProduct(Request $request, $idOrSlug = null)
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];

        $identifier = $idOrSlug
            ?? $request->input('product_id')
            ?? $request->input('productId')
            ?? $request->input('product_slug')
            ?? $request->input('slug')
            ?? ($bodyJson['product_id'] ?? null)
            ?? ($bodyJson['productId'] ?? null)
            ?? ($bodyJson['product_slug'] ?? null)
            ?? ($bodyJson['slug'] ?? null);

        if (empty($identifier)) {
            return null;
        }

        if (is_numeric($identifier)) {
            return Product::find($identifier);
        }

        return Product::where('slug', $identifier)->orWhere('sku', $identifier)->first();
    }

    /**
     * Format a single cart item.
     */
    protected function formatCartItem(CartItem $item)
    {
        $product = $item->product;
        if (!$product) {
            return null;
        }

        $qty = max(1, (int) $item->quantity);
        $unitMrp = (float) $product->mrp;
        $totalMrp = $unitMrp * $qty;

        $bookingPct = (float) ($product->booking_percentage ?: 20.00);
        $tokenAmount = round($totalMrp * ($bookingPct / 100), 2);
        $balancePct = 100.00 - $bookingPct;
        $balanceAmount = round($totalMrp - $tokenAmount, 2);

        $mainImageUrl = ImageHelper::resolve($product->main_image);
        $galleryUrls  = ImageHelper::resolveGallery($product->gallery, $product->main_image);

        // Tax breakdown (GST 18% inclusive in MRP)
        $taxRate = 18.0;
        $taxableAmount = round($totalMrp / (1 + ($taxRate / 100)), 2);
        $totalTax = round($totalMrp - $taxableAmount, 2);
        $cgst = round($totalTax / 2, 2);
        $sgst = round($totalTax / 2, 2);

        return [
            'cart_item_id'       => $item->id,
            'id'                 => $item->id,
            'product_id'         => $product->id,
            'name'               => $product->name,
            'slug'               => $product->slug,
            'model_code'         => $product->model_code,
            'sku'                => $product->sku,
            'selected_color'     => $item->selected_color,
            'quantity'           => $qty,
            'unit_mrp'           => $unitMrp,
            'item_total_mrp'     => $totalMrp,
            'token_percentage'   => $bookingPct,
            'token_amount'       => $tokenAmount,
            'balance_percentage' => $balancePct,
            'balance_amount'     => $balanceAmount,
            'tax_breakdown'      => [
                'tax_rate_pct'   => $taxRate,
                'taxable_amount' => $taxableAmount,
                'cgst_9pct'      => $cgst,
                'sgst_9pct'      => $sgst,
                'total_tax'      => $totalTax,
            ],
            'category'           => $product->category ? [
                'id'             => $product->category->id,
                'name'           => $product->category->name,
                'slug'           => $product->category->slug,
            ] : null,
            'stock'              => (int) $product->stock,
            'in_stock'           => (int) $product->stock >= $qty,
            'main_image'         => $mainImageUrl,
            'imageUrl'           => $mainImageUrl,
            'gallery'            => $galleryUrls,
            'offer_text'         => $product->offer_text,
            'created_at'         => $item->created_at ? $item->created_at->toIso8601String() : null,
            'updated_at'         => $item->updated_at ? $item->updated_at->toIso8601String() : null,
        ];
    }

    /**
     * Calculate summary for a collection of formatted cart items.
     */
    protected function calculateCartSummary($formattedItems)
    {
        $totalItems = 0;
        $subtotalMrp = 0.0;
        $totalToken = 0.0;
        $totalBalance = 0.0;

        foreach ($formattedItems as $item) {
            $totalItems += (int) $item['quantity'];
            $subtotalMrp += (float) $item['item_total_mrp'];
            $totalToken += (float) $item['token_amount'];
            $totalBalance += (float) $item['balance_amount'];
        }

        $taxRate = 18.0;
        $taxableAmount = round($subtotalMrp / (1 + ($taxRate / 100)), 2);
        $totalTax = round($subtotalMrp - $taxableAmount, 2);
        $cgst = round($totalTax / 2, 2);
        $sgst = round($totalTax / 2, 2);

        // Free delivery standard for all EV orders / orders over 5,000
        $deliveryCharge = 0.0;

        return [
            'unique_items'       => count($formattedItems),
            'total_quantity'     => $totalItems,
            'subtotal_mrp'       => round($subtotalMrp, 2),
            'token_percentage'   => 20.0,
            'total_token_amount' => round($totalToken, 2),
            'balance_percentage' => 80.0,
            'total_balance_amount'=> round($totalBalance, 2),
            'taxes'              => [
                'tax_rate_pct'   => $taxRate,
                'taxable_amount' => $taxableAmount,
                'cgst'           => $cgst,
                'sgst'           => $sgst,
                'total_tax'      => $totalTax,
            ],
            'delivery_fee'       => $deliveryCharge,
            'delivery_text'      => 'FREE Delivery',
            'payable_now'        => round($totalToken, 2),
            'payable_later'      => round($totalBalance, 2),
            'grand_total'        => round($subtotalMrp + $deliveryCharge, 2),
        ];
    }

    /**
     * GET or POST /api/cart or /api/customer/cart
     * View customer's cart items and financial summary.
     */
    public function index(Request $request)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $items = CartItem::with(['product.category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->get();

            $formatted = $items->map(fn($item) => $this->formatCartItem($item))->filter()->values();
            $summary = $this->calculateCartSummary($formatted);

            return response()->json([
                'status'  => true,
                'message' => 'Cart items retrieved successfully.',
                'summary' => $summary,
                'data'    => $formatted,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve cart items.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/cart/add or /api/cart
     * Add product to customer's cart.
     */
    public function store(Request $request)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $product = $this->resolveProduct($request);
            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found. Please provide a valid product_id or productId.',
                ], 404);
            }

            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $color = $request->input('selected_color')
                ?? $request->input('selectedColor')
                ?? $request->input('color')
                ?? ($bodyJson['selected_color'] ?? null)
                ?? ($bodyJson['selectedColor'] ?? null)
                ?? ($bodyJson['color'] ?? null);

            $quantity = (int) (
                $request->input('quantity')
                ?? ($bodyJson['quantity'] ?? 1)
            );
            if ($quantity < 1) {
                $quantity = 1;
            }

            // Check if same item & color already in cart
            $cartItem = CartItem::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->when($color, function ($q) use ($color) {
                    return $q->where('selected_color', $color);
                }, function ($q) {
                    return $q->whereNull('selected_color');
                })
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
                $cartItem->refresh();
                $message = 'Cart item quantity updated successfully.';
            } else {
                $cartItem = CartItem::create([
                    'user_id'        => $customer->id,
                    'product_id'     => $product->id,
                    'selected_color' => $color,
                    'quantity'       => $quantity,
                ]);
                $message = 'Product added to cart successfully.';
            }

            $allFormatted = CartItem::with(['product.category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->get()
                ->map(fn($item) => $this->formatCartItem($item))
                ->filter()
                ->values();

            return response()->json([
                'status'  => true,
                'message' => $message,
                'item'    => $this->formatCartItem($cartItem),
                'summary' => $this->calculateCartSummary($allFormatted),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to add product to cart.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST or PUT /api/cart/update
     * Update quantity or selected color of a cart item.
     */
    public function update(Request $request)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $cartItemId = $request->input('cart_item_id')
                ?? $request->input('cartItemId')
                ?? $request->input('id')
                ?? ($bodyJson['cart_item_id'] ?? null)
                ?? ($bodyJson['cartItemId'] ?? null)
                ?? ($bodyJson['id'] ?? null);

            $cartItem = null;
            if ($cartItemId) {
                $cartItem = CartItem::where('id', $cartItemId)->where('user_id', $customer->id)->first();
            }

            if (!$cartItem) {
                $product = $this->resolveProduct($request);
                if ($product) {
                    $cartItem = CartItem::where('user_id', $customer->id)->where('product_id', $product->id)->first();
                }
            }

            if (!$cartItem) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cart item not found.',
                ], 404);
            }

            // Quantity update
            if ($request->has('quantity') || isset($bodyJson['quantity'])) {
                $newQty = (int) ($request->input('quantity') ?? $bodyJson['quantity']);
                if ($newQty <= 0) {
                    $cartItem->delete();

                    $remaining = CartItem::with(['product.category'])
                        ->where('user_id', $customer->id)
                        ->latest()
                        ->get()
                        ->map(fn($item) => $this->formatCartItem($item))
                        ->filter()
                        ->values();

                    return response()->json([
                        'status'  => true,
                        'message' => 'Item removed from cart (quantity 0).',
                        'summary' => $this->calculateCartSummary($remaining),
                    ], 200);
                }
                $cartItem->quantity = $newQty;
            }

            // Color update
            if ($request->has('selected_color') || isset($bodyJson['selected_color']) || $request->has('selectedColor') || isset($bodyJson['selectedColor'])) {
                $cartItem->selected_color = $request->input('selected_color')
                    ?? $request->input('selectedColor')
                    ?? ($bodyJson['selected_color'] ?? null)
                    ?? ($bodyJson['selectedColor'] ?? null);
            }

            $cartItem->save();
            $cartItem->refresh();

            $allFormatted = CartItem::with(['product.category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->get()
                ->map(fn($item) => $this->formatCartItem($item))
                ->filter()
                ->values();

            return response()->json([
                'status'  => true,
                'message' => 'Cart item updated successfully.',
                'item'    => $this->formatCartItem($cartItem),
                'summary' => $this->calculateCartSummary($allFormatted),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update cart item.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE or POST /api/cart/remove or /api/cart/{id}
     * Remove an item or product from cart.
     */
    public function destroy(Request $request, $id = null)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $cartItemId = $id
                ?? $request->input('cart_item_id')
                ?? $request->input('cartItemId')
                ?? ($bodyJson['cart_item_id'] ?? null)
                ?? ($bodyJson['cartItemId'] ?? null);

            $deleted = 0;

            if ($cartItemId) {
                $deleted = CartItem::where('id', $cartItemId)->where('user_id', $customer->id)->delete();
            } else {
                $product = $this->resolveProduct($request);
                if ($product) {
                    $color = $request->input('selected_color')
                        ?? $request->input('selectedColor')
                        ?? ($bodyJson['selected_color'] ?? null)
                        ?? ($bodyJson['selectedColor'] ?? null);

                    $q = CartItem::where('user_id', $customer->id)->where('product_id', $product->id);
                    if ($color) {
                        $q->where('selected_color', $color);
                    }
                    $deleted = $q->delete();
                }
            }

            $remaining = CartItem::with(['product.category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->get()
                ->map(fn($item) => $this->formatCartItem($item))
                ->filter()
                ->values();

            return response()->json([
                'status'  => true,
                'message' => $deleted > 0 ? 'Item removed from cart successfully.' : 'Item not found in your cart.',
                'deleted_count' => $deleted,
                'summary' => $this->calculateCartSummary($remaining),
                'remaining_items' => $remaining,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to remove item from cart.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE or POST /api/cart/clear
     * Clear all items in customer's cart.
     */
    public function clear(Request $request)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $count = CartItem::where('user_id', $customer->id)->delete();

            return response()->json([
                'status'  => true,
                'message' => "Cart cleared successfully. {$count} item(s) removed.",
                'summary' => [
                    'unique_items'       => 0,
                    'total_quantity'     => 0,
                    'subtotal_mrp'       => 0.0,
                    'payable_now'        => 0.0,
                    'payable_later'      => 0.0,
                    'grand_total'        => 0.0,
                ],
                'data'    => [],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to clear cart.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
