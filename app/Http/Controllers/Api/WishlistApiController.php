<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class WishlistApiController extends Controller
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
     * Format a product for wishlist response.
     */
    protected function formatWishlistItem(Wishlist $wishlist)
    {
        $product = $wishlist->product;
        if (!$product) {
            return null;
        }

        $mainImageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);
        $galleryUrls  = \App\Helpers\ImageHelper::resolveGallery($product->gallery, $product->main_image);

        $mrp = (float) $product->mrp;
        $bookingPct = (float) ($product->booking_percentage ?? 20.00);
        $bookingAmount = (float) ($product->booking_amount > 0 ? $product->booking_amount : ($mrp * ($bookingPct / 100)));
        $balanceAmount = (float) ($product->balance_amount > 0 ? $product->balance_amount : ($mrp - $bookingAmount));
        $eligibleValue = (float) ($product->eligible_referral_value ?: $mrp);

        return [
            'wishlist_id'             => $wishlist->id,
            'added_at'                => $wishlist->created_at ? $wishlist->created_at->toIso8601String() : null,
            'id'                      => $product->id,
            'product_id'              => $product->id,
            'name'                    => $product->name,
            'slug'                    => $product->slug,
            'model_code'              => $product->model_code,
            'sku'                     => $product->sku,
            'category'                => $product->category ? [
                'id'                     => $product->category->id,
                'name'                   => $product->category->name,
                'slug'                   => $product->category->slug,
                'referral_category_code' => $product->category->referral_category_code,
                'category_code'          => $product->category->referral_category_code,
                'referral_code'          => $product->category->referral_category_code,
            ] : null,
            'mrp'                     => $mrp,
            'booking_percentage'      => $bookingPct,
            'booking_amount'          => $bookingAmount,
            'balance_amount'          => $balanceAmount,
            'stock'                   => (int) $product->stock,
            'in_stock'                => (int) $product->stock > 0,
            'stock_status'            => (int) $product->stock > 0 ? 'In Stock' : 'Out of Stock',
            'image_path'              => $product->main_image,
            'main_image'              => $mainImageUrl,
            'imageUrl'                => $mainImageUrl,
            'images'                  => $galleryUrls,
            'gallery'                 => $galleryUrls,
            'offer_text'              => $product->offer_text,
            'is_featured'             => (bool) $product->is_featured,
            'referral_eligible'       => (bool) $product->referral_eligible,
            'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
            'eligible_referral_value' => $eligibleValue,
            'self_dealer_benefit'     => [
                'activates_self_dealer'   => (bool) $product->self_dealer_eligible,
                'activation_points_pct'   => 20.00,
                'activation_points_value' => $eligibleValue * 0.20,
            ],
            'status'                  => $product->status,
        ];
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
     * GET /api/customer/wishlist or /api/wishlist
     * Fetch user's wishlist / favorites items.
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

            $wishlistItems = Wishlist::with(['product.category'])
                ->where('user_id', $customer->id)
                ->latest()
                ->get();

            $formatted = $wishlistItems->map(fn($item) => $this->formatWishlistItem($item))->filter()->values();

            return response()->json([
                'status'  => true,
                'message' => 'Wishlist items retrieved successfully.',
                'total'   => $formatted->count(),
                'data'    => $formatted,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve wishlist.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/customer/wishlist or /api/wishlist
     * Add an item to user's wishlist / favorites.
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
                // If no product_id is provided, automatically return the user's wishlist list
                return $this->index($request);
            }

            $existing = Wishlist::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'status'         => true,
                    'message'        => 'Product is already in your wishlist.',
                    'is_in_wishlist' => true,
                    'data'           => $this->formatWishlistItem($existing),
                ], 200);
            }

            $wishlist = Wishlist::create([
                'user_id'    => $customer->id,
                'product_id' => $product->id,
            ]);

            return response()->json([
                'status'         => true,
                'message'        => 'Product added to wishlist successfully.',
                'is_in_wishlist' => true,
                'data'           => $this->formatWishlistItem($wishlist),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to add product to wishlist.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/customer/wishlist/toggle or /api/wishlist/toggle
     * 1-Click Toggle: Adds if not saved; Removes if already saved.
     */
    public function toggle(Request $request)
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
                    'message' => 'Product not found. Please provide a valid product_id or slug.',
                ], 404);
            }

            $existing = Wishlist::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->delete();

                return response()->json([
                    'status'         => true,
                    'message'        => 'Product removed from wishlist.',
                    'is_in_wishlist' => false,
                    'product_id'     => $product->id,
                    'total'          => Wishlist::where('user_id', $customer->id)->count(),
                ], 200);
            }

            $newWishlist = Wishlist::create([
                'user_id'    => $customer->id,
                'product_id' => $product->id,
            ]);

            return response()->json([
                'status'         => true,
                'message'        => 'Product added to wishlist.',
                'is_in_wishlist' => true,
                'product_id'     => $product->id,
                'total'          => Wishlist::where('user_id', $customer->id)->count(),
                'data'           => $this->formatWishlistItem($newWishlist),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to toggle wishlist item.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE or POST /api/customer/wishlist/{productId}
     * Remove an item from wishlist.
     */
    public function destroy(Request $request, $productId = null)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $product = $this->resolveProduct($request, $productId);
            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found or invalid product ID.',
                ], 404);
            }

            $deleted = Wishlist::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->delete();

            return response()->json([
                'status'         => true,
                'message'        => $deleted ? 'Product removed from wishlist successfully.' : 'Product was not in your wishlist.',
                'is_in_wishlist' => false,
                'product_id'     => $product->id,
                'total'          => Wishlist::where('user_id', $customer->id)->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to remove product from wishlist.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE or POST /api/customer/wishlist/clear
     * Clear all saved items in user's wishlist.
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

            $count = Wishlist::where('user_id', $customer->id)->delete();

            return response()->json([
                'status'  => true,
                'message' => "Wishlist cleared successfully. {$count} item(s) removed.",
                'total'   => 0,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to clear wishlist.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/wishlist/check/{productId}
     * Check if a specific product is saved in user's favorites.
     */
    public function check(Request $request, $productId = null)
    {
        try {
            $customer = $this->resolveCustomer($request);
            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
                ], 401);
            }

            $product = $this->resolveProduct($request, $productId);
            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            $exists = Wishlist::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->exists();

            return response()->json([
                'status'         => true,
                'product_id'     => $product->id,
                'is_in_wishlist' => $exists,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to check wishlist status.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
