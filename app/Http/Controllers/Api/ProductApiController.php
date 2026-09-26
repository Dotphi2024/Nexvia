<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * GET /api/products or /api/customer/products
     * Fetch paginated product listing with filters: category, search, sortBy, page, limit
     */
    public function index(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $categoryInput = $request->query('cat_id')
                ?? $request->query('catId')
                ?? $request->query('category_id')
                ?? $request->query('categoryId')
                ?? $request->query('cat')
                ?? $request->query('category')
                ?? $request->input('cat_id')
                ?? $request->input('catId')
                ?? $request->input('category_id')
                ?? $request->input('categoryId')
                ?? $request->input('cat')
                ?? $request->input('category')
                ?? ($bodyJson['cat_id'] ?? null)
                ?? ($bodyJson['catId'] ?? null)
                ?? ($bodyJson['category_id'] ?? null)
                ?? ($bodyJson['categoryId'] ?? null)
                ?? ($bodyJson['cat'] ?? null)
                ?? ($bodyJson['category'] ?? null);

            $search = $request->input('search')
                ?? ($bodyJson['search'] ?? null);

            $sortBy = $request->input('sortBy')
                ?? $request->input('sort_by')
                ?? ($bodyJson['sortBy'] ?? null)
                ?? ($bodyJson['sort_by'] ?? null);

            $page = (int) ($request->input('page') ?? ($bodyJson['page'] ?? 1));
            $perPage = (int) ($request->input('limit') ?? $request->input('per_page') ?? ($bodyJson['limit'] ?? null) ?? ($bodyJson['per_page'] ?? 10));
            $perPage = max(1, min(100, $perPage));

            $query = Product::with('category')->where('status', 'active');

            // 0. Filter by Type (e.g. dls_farm_equipment / dls_agro vs standard)
            $typeInput = $request->query('type')
                ?? $request->input('type')
                ?? ($bodyJson['type'] ?? null);

            if (!empty($typeInput)) {
                $typeInput = strtolower(trim((string)$typeInput));
                if (in_array($typeInput, ['dls_farm_equipment', 'dls_agro', 'agro', 'farm', 'dls'])) {
                    $query->whereHas('category', function ($q) {
                        $q->where('type', 'dls_farm_equipment');
                    });
                } elseif (in_array($typeInput, ['standard', 'regular', 'general', 'main'])) {
                    $query->whereHas('category', function ($q) {
                        $q->where('type', '!=', 'dls_farm_equipment');
                    });
                } else {
                    $query->whereHas('category', function ($q) use ($typeInput) {
                        $q->where('type', $typeInput);
                    });
                }
            }

            // 1. Filter by Category (accepts category_id or category slug)
            if (!empty($categoryInput)) {
                $categoryInput = trim($categoryInput);
                if (is_numeric($categoryInput)) {
                    $query->where('category_id', $categoryInput);
                } else {
                    $query->whereHas('category', function ($q) use ($categoryInput) {
                        $q->where('slug', $categoryInput)->orWhere('name', 'like', "%{$categoryInput}%");
                    });
                }
            }

            // 2. Search Keyword Filter
            if (!empty($search)) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model_code', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('offer_text', 'like', "%{$search}%");
                });
            }

            // 3. Sorting Filter
            $sortBy = strtolower(trim((string)$sortBy));

            switch ($sortBy) {
                case 'price-low':
                case 'price_low':
                case 'price_asc':
                    $query->orderBy('mrp', 'asc');
                    break;

                case 'price-high':
                case 'price_high':
                case 'price_desc':
                    $query->orderBy('mrp', 'desc');
                    break;

                case 'rating':
                case 'popular':
                case 'featured':
                    $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                    break;

                default:
                    $query->orderBy('id', 'desc');
                    break;
            }

            $products = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedProducts = collect($products->items())->map(function ($product) {
                $imageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);
                $galleryUrls = \App\Helpers\ImageHelper::resolveGallery($product->gallery, $product->main_image);

                return [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'slug'               => $product->slug,
                    'model_code'         => $product->model_code,
                    'sku'                => $product->sku,
                    'category'           => $product->category ? [
                        'id'                     => $product->category->id,
                        'name'                   => $product->category->name,
                        'slug'                   => $product->category->slug,
                        'referral_category_code' => $product->category->referral_category_code,
                        'category_code'          => $product->category->referral_category_code,
                        'referral_code'          => $product->category->referral_category_code,
                    ] : null,
                    'mrp'                => (float) $product->mrp,
                    'booking_percentage' => (float) ($product->booking_percentage ?? 20.00),
                    'booking_amount'     => (float) $product->booking_amount,
                    'balance_amount'     => (float) $product->balance_amount,
                    'stock'              => (int) $product->stock,
                    'main_image'         => $imageUrl,
                    'imageUrl'           => $imageUrl,
                    'images'             => $galleryUrls,
                    'gallery'            => $galleryUrls,
                    'video_url'          => $product->video_url,
                    'offer_text'         => $product->offer_text,
                    'overview'           => $product->overview ?? $product->description,
                    'description'        => $product->overview ?? $product->description,
                    'features'           => is_array($product->key_features) ? $product->key_features : [],
                    'key_features'       => is_array($product->key_features) ? $product->key_features : [],
                    'specs'              => is_array($product->specs) ? $product->specs : [],
                    'technical_specifications' => $product->technical_specifications ?: [],
                    'is_featured'        => (bool) $product->is_featured,
                    'status'             => $product->status,
                    'eligible_referral_value' => (float) ($product->eligible_referral_value ?: $product->mrp),
                    'referral_eligible'       => (bool) $product->referral_eligible,
                    'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
                    'self_dealer_benefit'     => [
                        'activates_self_dealer'  => (bool) $product->self_dealer_eligible,
                        'activation_points_pct'  => 20.00,
                        'activation_points_value'=> ((float) ($product->eligible_referral_value ?: $product->mrp)) * 0.20,
                    ],
                    'created_at'         => $product->created_at ? $product->created_at->toIso8601String() : null,
                ];
            });

            return response()->json([
                'status'     => true,
                'message'    => 'Products retrieved successfully.',
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                    'has_more'     => $products->hasMorePages(),
                ],
                'data'       => $formattedProducts,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve products.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/products/{slugOrId} or /api/customer/products/{slugOrId}
     * Fetch single product full details, pricing, referral benefit, media gallery & specifications.
     */
    public function show($slugOrId)
    {
        try {
            $slugOrId = trim($slugOrId);

            $product = Product::with('category')
                ->where('status', 'active')
                ->where(function ($q) use ($slugOrId) {
                    if (is_numeric($slugOrId)) {
                        $q->where('id', $slugOrId);
                    } else {
                        $q->where('slug', $slugOrId)
                          ->orWhere('model_code', $slugOrId)
                          ->orWhere('sku', $slugOrId);
                    }
                })
                ->first();

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Images resolution with fallback
            $mainImageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);
            $galleryUrls  = \App\Helpers\ImageHelper::resolveGallery($product->gallery, $product->main_image);

            // Pricing & 20% Booking Calculations
            $mrp = (float) $product->mrp;
            $bookingPct = (float) ($product->booking_percentage ?? 20.00);
            $bookingAmount = (float) ($product->booking_amount > 0 ? $product->booking_amount : ($mrp * ($bookingPct / 100)));
            $balanceAmount = (float) ($product->balance_amount > 0 ? $product->balance_amount : ($mrp - $bookingAmount));

            // Self Dealer & Referral Benefit
            $eligibleValue = (float) ($product->eligible_referral_value ?: $mrp);
            $activationPts = $eligibleValue * 0.20;

            // Specifications Formatting
            $specsMap = $product->specs ?? [];
            if (is_string($specsMap)) {
                $specsMap = json_decode($specsMap, true) ?? [];
            }

            // Format as array of key-value objects for mobile table rendering
            $specificationsList = [];
            if (is_array($specsMap)) {
                foreach ($specsMap as $key => $val) {
                    $specificationsList[] = [
                        'name'  => (string) $key,
                        'value' => is_array($val) ? implode(', ', $val) : (string) $val,
                    ];
                }
            }

            $keyFeatures = $product->key_features ?? [];
            if (is_string($keyFeatures)) {
                $keyFeatures = json_decode($keyFeatures, true) ?? [];
            }

            // Stock & Inventory
            $stockQty = (int) $product->stock;
            $inStock  = $stockQty > 0;

            return response()->json([
                'status'  => true,
                'message' => 'Product full details & specifications retrieved successfully.',
                'data'    => [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'slug'               => $product->slug,
                    'model_code'         => $product->model_code,
                    'sku'                => $product->sku,

                    // Category Information & Referral Code
                    'category'           => $product->category ? [
                        'id'                     => $product->category->id,
                        'name'                   => $product->category->name,
                        'slug'                   => $product->category->slug,
                        'referral_category_code' => $product->category->referral_category_code,
                        'category_code'          => $product->category->referral_category_code,
                        'referral_code'          => $product->category->referral_category_code,
                    ] : null,

                    // Pricing Details & 20% Booking Structure
                    'mrp'                => $mrp,
                    'booking_percentage' => $bookingPct,
                    'booking_amount'     => $bookingAmount,
                    'balance_amount'     => $balanceAmount,
                    'balance_due_days'   => 60,
                    'pricing'            => [
                        'mrp'                => $mrp,
                        'booking_percentage' => $bookingPct,
                        'booking_amount'     => $bookingAmount,
                        'balance_amount'     => $balanceAmount,
                        'balance_due_days'   => 60,
                        'currency'           => 'INR',
                        'currency_symbol'    => '₹',
                        'pricing_summary'    => "Pay ₹" . number_format($bookingAmount, 2) . " ({$bookingPct}%) now, pay balance ₹" . number_format($balanceAmount, 2) . " within 60 days.",
                    ],

                    // Self Dealer & Referral Program
                    'referral_eligible'       => (bool) $product->referral_eligible,
                    'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
                    'eligible_referral_value' => $eligibleValue,
                    'self_dealer_benefit'     => [
                        'activates_self_dealer'   => (bool) $product->self_dealer_eligible,
                        'activation_points_pct'   => 20.00,
                        'activation_points_value' => $activationPts,
                        'banner_title'            => 'SELF DEALER BENEFIT',
                        'banner_text'             => "Book this product and become an eligible Self Dealer. Earn ₹" . number_format($activationPts, 2) . " (20%) in activation points!",
                    ],

                    // Stock & Availability
                    'stock'              => $stockQty,
                    'in_stock'           => $inStock,
                    'stock_status'       => $inStock ? 'In Stock' : 'Out of Stock',

                    // Media & Gallery
                    'main_image'         => $mainImageUrl,
                    'imageUrl'           => $mainImageUrl,
                    'gallery'            => $galleryUrls,
                    'video_url'          => $product->video_url,

                    // Features & Specifications
                    'overview'           => $product->overview ?? $product->description,
                    'description'        => $product->overview ?? $product->description,
                    'features'           => $keyFeatures,
                    'key_features'       => $keyFeatures,
                    'specs'              => $specsMap,
                    'specifications'     => $specificationsList,
                    'technical_specifications' => $specificationsList,

                    // Warranty, Installation & Delivery
                    'warranty_info'      => $product->warranty_info,
                    'installation_info'  => $product->installation_info,
                    'delivery_info'      => $product->delivery_info,
                    'services'           => [
                        'warranty'     => $product->warranty_info,
                        'installation' => $product->installation_info,
                        'delivery'     => $product->delivery_info,
                    ],

                    // Metadata
                    'offer_text'         => $product->offer_text,
                    'is_featured'        => (bool) $product->is_featured,
                    'status'             => $product->status,
                    'created_at'         => $product->created_at ? $product->created_at->toIso8601String() : null,
                    'updated_at'         => $product->updated_at ? $product->updated_at->toIso8601String() : null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve product details.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET/POST /api/products/search or /api/customer/products/search
     * Dedicated Product Search & Filter endpoint.
     * Any parameter or combination will filter and return results accordingly.
     * Supported parameters:
     * - q / query / search / keyword
     * - id / product_id
     * - category / category_id / category_code / cat / referral_category_code
     * - model / model_code
     * - sku
     * - slug
     * - min_price / minPrice / price_from
     * - max_price / maxPrice / price_to
     * - referral_eligible / referral
     * - self_dealer_eligible / self_dealer
     * - is_featured / featured
     * - in_stock / stock
     * - sortBy / sort_by / sort / order_by
     * - page & limit / per_page
     */
    public function search(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $trimmedJson = [];
            if (is_array($bodyJson)) {
                foreach ($bodyJson as $k => $v) {
                    $trimmedJson[trim($k)] = $v;
                }
            }

            // 1. Keyword search parameter
            $searchTerm = trim((string) (
                $request->input('q')
                ?? $request->input('query')
                ?? $request->input('search')
                ?? $request->input('keyword')
                ?? ($trimmedJson['q'] ?? null)
                ?? ($trimmedJson['query'] ?? null)
                ?? ($trimmedJson['search'] ?? null)
                ?? ($trimmedJson['keyword'] ?? null)
                ?? ''
            ));

            // 2. Specific Product ID
            $productId = $request->input('id')
                ?? $request->input('product_id')
                ?? ($trimmedJson['id'] ?? null)
                ?? ($trimmedJson['product_id'] ?? null);

            // 3. Category Filter
            $categoryInput = trim((string) (
                $request->query('cat_id')
                ?? $request->query('catId')
                ?? $request->query('category_id')
                ?? $request->query('categoryId')
                ?? $request->input('cat_id')
                ?? $request->input('catId')
                ?? $request->input('categoryId')
                ?? $request->input('category')
                ?? $request->input('category_id')
                ?? $request->input('category_code')
                ?? $request->input('referral_category_code')
                ?? $request->input('cat')
                ?? ($trimmedJson['cat_id'] ?? null)
                ?? ($trimmedJson['catId'] ?? null)
                ?? ($trimmedJson['categoryId'] ?? null)
                ?? ($trimmedJson['category'] ?? null)
                ?? ($trimmedJson['category_id'] ?? null)
                ?? ($trimmedJson['category_code'] ?? null)
                ?? ($trimmedJson['referral_category_code'] ?? null)
                ?? ($trimmedJson['cat'] ?? null)
                ?? ''
            ));

            // 4. Model code & SKU & Slug
            $modelCode = trim((string) ($request->input('model_code') ?? $request->input('model') ?? ($trimmedJson['model_code'] ?? null) ?? ($trimmedJson['model'] ?? null) ?? ''));
            $sku = trim((string) ($request->input('sku') ?? ($trimmedJson['sku'] ?? null) ?? ''));
            $slug = trim((string) ($request->input('slug') ?? ($trimmedJson['slug'] ?? null) ?? ''));

            // 5. Price range
            $minPrice = $request->input('min_price') ?? $request->input('minPrice') ?? $request->input('price_from') ?? ($trimmedJson['min_price'] ?? null) ?? ($trimmedJson['minPrice'] ?? null) ?? ($trimmedJson['price_from'] ?? null);
            $maxPrice = $request->input('max_price') ?? $request->input('maxPrice') ?? $request->input('price_to') ?? ($trimmedJson['max_price'] ?? null) ?? ($trimmedJson['maxPrice'] ?? null) ?? ($trimmedJson['price_to'] ?? null);

            // 6. Referral & Self Dealer flags
            $refEligible = $request->input('referral_eligible') ?? $request->input('referral') ?? ($trimmedJson['referral_eligible'] ?? null) ?? ($trimmedJson['referral'] ?? null);
            $selfDealerEligible = $request->input('self_dealer_eligible') ?? $request->input('self_dealer') ?? ($trimmedJson['self_dealer_eligible'] ?? null) ?? ($trimmedJson['self_dealer'] ?? null);

            // 7. Featured & Stock flags
            $featured = $request->input('is_featured') ?? $request->input('featured') ?? ($trimmedJson['is_featured'] ?? null) ?? ($trimmedJson['featured'] ?? null);
            $inStock = $request->input('in_stock') ?? $request->input('stock') ?? ($trimmedJson['in_stock'] ?? null) ?? ($trimmedJson['stock'] ?? null);

            // 8. Sorting
            $sortBy = strtolower(trim((string) (
                $request->input('sortBy')
                ?? $request->input('sort_by')
                ?? $request->input('sort')
                ?? $request->input('order_by')
                ?? ($trimmedJson['sortBy'] ?? null)
                ?? ($trimmedJson['sort_by'] ?? null)
                ?? ($trimmedJson['sort'] ?? null)
                ?? 'relevance'
            )));

            // 9. Pagination
            $page = (int) ($request->input('page') ?? ($trimmedJson['page'] ?? 1));
            $perPage = (int) ($request->input('limit') ?? $request->input('per_page') ?? $request->input('perPage') ?? $request->input('size') ?? ($trimmedJson['limit'] ?? null) ?? ($trimmedJson['per_page'] ?? null) ?? 20);
            $perPage = max(1, min(100, $perPage));

            // Start base query
            $query = Product::with('category')->where('status', 'active');

            // Apply: Direct ID filter
            if (!empty($productId) && is_numeric($productId)) {
                $query->where('id', $productId);
            }

            // Apply: Direct Slug filter
            if (!empty($slug)) {
                $query->where('slug', $slug);
            }

            // Apply: Model Code filter
            if (!empty($modelCode)) {
                $query->where('model_code', 'like', "%{$modelCode}%");
            }

            // Apply: SKU filter
            if (!empty($sku)) {
                $query->where('sku', 'like', "%{$sku}%");
            }

            // Apply: Text Search across name, model_code, sku, offer_text, slug, specs, and category
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('model_code', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%")
                      ->orWhere('offer_text', 'like', "%{$searchTerm}%")
                      ->orWhere('slug', 'like', "%{$searchTerm}%")
                      ->orWhereHas('category', function ($catQ) use ($searchTerm) {
                          $catQ->where('name', 'like', "%{$searchTerm}%")
                               ->orWhere('referral_category_code', 'like', "%{$searchTerm}%")
                               ->orWhere('slug', 'like', "%{$searchTerm}%");
                      });
                });
            }

            // Apply: Category Filter (Accepts numeric ID, category slug, or referral category code like TV)
            if (!empty($categoryInput)) {
                if (is_numeric($categoryInput)) {
                    $query->where('category_id', $categoryInput);
                } else {
                    $query->whereHas('category', function ($q) use ($categoryInput) {
                        $q->where('slug', $categoryInput)
                          ->orWhere('referral_category_code', strtoupper($categoryInput))
                          ->orWhere('name', 'like', "%{$categoryInput}%");
                    });
                }
            }

            // Apply: Price Range Filters
            if (!is_null($minPrice) && is_numeric($minPrice)) {
                $query->where('mrp', '>=', (float) $minPrice);
            }
            if (!is_null($maxPrice) && is_numeric($maxPrice)) {
                $query->where('mrp', '<=', (float) $maxPrice);
            }

            // Apply: Referral Eligibility Filter
            if (!is_null($refEligible) && $refEligible !== '') {
                $query->where('referral_eligible', filter_var($refEligible, FILTER_VALIDATE_BOOLEAN));
            }

            // Apply: Self Dealer Eligibility Filter
            if (!is_null($selfDealerEligible) && $selfDealerEligible !== '') {
                $query->where('self_dealer_eligible', filter_var($selfDealerEligible, FILTER_VALIDATE_BOOLEAN));
            }

            // Apply: Featured Filter
            if (!is_null($featured) && $featured !== '') {
                $query->where('is_featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN));
            }

            // Apply: In-Stock Filter
            if (!is_null($inStock) && $inStock !== '') {
                $stockBool = filter_var($inStock, FILTER_VALIDATE_BOOLEAN);
                if ($stockBool) {
                    $query->where('stock', '>', 0);
                } else {
                    $query->where('stock', '<=', 0);
                }
            }

            // Apply: Sorting
            switch ($sortBy) {
                case 'price-low':
                case 'price_low':
                case 'price_asc':
                    $query->orderBy('mrp', 'asc');
                    break;

                case 'price-high':
                case 'price_high':
                case 'price_desc':
                    $query->orderBy('mrp', 'desc');
                    break;

                case 'newest':
                case 'latest':
                    $query->orderBy('id', 'desc');
                    break;

                case 'name_asc':
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;

                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;

                case 'featured':
                    $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                    break;

                default:
                    $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                    break;
            }

            // Execute Pagination
            $results = $query->paginate($perPage, ['*'], 'page', $page);

            // Format Output
            $formatted = collect($results->items())->map(function ($product) {
                $imageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);
                $galleryUrls = \App\Helpers\ImageHelper::resolveGallery($product->gallery, $product->main_image);

                $mrp = (float) $product->mrp;
                $bookingPct = (float) ($product->booking_percentage ?? 20.00);
                $bookingAmount = (float) ($product->booking_amount > 0 ? $product->booking_amount : ($mrp * ($bookingPct / 100)));
                $balanceAmount = (float) ($product->balance_amount > 0 ? $product->balance_amount : ($mrp - $bookingAmount));
                $eligibleVal = (float) ($product->eligible_referral_value ?: $mrp);

                return [
                    'id'                      => $product->id,
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
                    'balance_due_days'        => 60,
                    'stock'                   => (int) $product->stock,
                    'in_stock'                => (int) $product->stock > 0,
                    'stock_status'            => (int) $product->stock > 0 ? 'In Stock' : 'Out of Stock',
                    'main_image'              => $imageUrl,
                    'imageUrl'                => $imageUrl,
                    'images'                  => $galleryUrls,
                    'gallery'                 => $galleryUrls,
                    'offer_text'              => $product->offer_text,
                    'overview'                => $product->overview ?? $product->description,
                    'description'             => $product->overview ?? $product->description,
                    'features'                => is_array($product->key_features) ? $product->key_features : [],
                    'key_features'            => is_array($product->key_features) ? $product->key_features : [],
                    'specs'                   => is_array($product->specs) ? $product->specs : [],
                    'technical_specifications'=> $product->technical_specifications ?: [],
                    'is_featured'             => (bool) $product->is_featured,
                    'referral_eligible'       => (bool) $product->referral_eligible,
                    'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
                    'eligible_referral_value' => $eligibleVal,
                    'self_dealer_benefit'     => [
                        'activates_self_dealer'   => (bool) $product->self_dealer_eligible,
                        'activation_points_pct'   => 20.00,
                        'activation_points_value' => $eligibleVal * 0.20,
                    ],
                ];
            });

            return response()->json([
                'status'           => true,
                'message'          => 'Search results retrieved successfully.',
                'total'            => $results->total(),
                'applied_filters'  => [
                    'query'                => $searchTerm ?: null,
                    'category'             => $categoryInput ?: null,
                    'model_code'           => $modelCode ?: null,
                    'sku'                  => $sku ?: null,
                    'min_price'            => $minPrice ? (float) $minPrice : null,
                    'max_price'            => $maxPrice ? (float) $maxPrice : null,
                    'referral_eligible'    => !is_null($refEligible) && $refEligible !== '' ? filter_var($refEligible, FILTER_VALIDATE_BOOLEAN) : null,
                    'self_dealer_eligible' => !is_null($selfDealerEligible) && $selfDealerEligible !== '' ? filter_var($selfDealerEligible, FILTER_VALIDATE_BOOLEAN) : null,
                    'is_featured'          => !is_null($featured) && $featured !== '' ? filter_var($featured, FILTER_VALIDATE_BOOLEAN) : null,
                    'in_stock'             => !is_null($inStock) && $inStock !== '' ? filter_var($inStock, FILTER_VALIDATE_BOOLEAN) : null,
                    'sort_by'              => $sortBy,
                ],
                'pagination'       => [
                    'current_page' => $results->currentPage(),
                    'last_page'    => $results->lastPage(),
                    'per_page'     => $results->perPage(),
                    'total'        => $results->total(),
                    'has_more'     => $results->hasMorePages(),
                ],
                'data'             => $formatted,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Product search failed.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET/POST /api/products/trending or /api/customer/products/trending
     * Fetch trending & lightning deal products.
     * Query parameter: ?limit=6
     */
    public function trending(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $rawLimit = $request->input('limit')
                ?? $request->input('limi')
                ?? $request->input('count')
                ?? $request->input('per_page')
                ?? $request->input('size')
                ?? ($bodyJson['limit'] ?? null)
                ?? ($bodyJson['limi'] ?? null)
                ?? ($bodyJson['count'] ?? null)
                ?? ($bodyJson['per_page'] ?? null);

            $limit = ($rawLimit !== null && is_numeric($rawLimit) && (int) $rawLimit > 0)
                ? (int) $rawLimit
                : 6;

            $limit = max(1, min(100, $limit));

            $query = Product::with('category')->where('status', 'active');

            // Optional category filter (e.g. ?category=tv or ?category=1)
            $categoryInput = $request->input('category') ?? $request->input('category_id') ?? ($bodyJson['category'] ?? null);
            if (!empty($categoryInput)) {
                $categoryInput = trim($categoryInput);
                if (is_numeric($categoryInput)) {
                    $query->where('category_id', $categoryInput);
                } else {
                    $query->whereHas('category', function ($q) use ($categoryInput) {
                        $q->where('slug', $categoryInput)
                          ->orWhere('referral_category_code', strtoupper($categoryInput))
                          ->orWhere('name', 'like', "%{$categoryInput}%");
                    });
                }
            }

            // Optional featured/trending only filter
            if ($request->boolean('featured_only') || $request->boolean('trending_only')) {
                $query->where('is_featured', true);
            }

            // Optional in_stock filter
            if ($request->boolean('in_stock')) {
                $query->where('stock', '>', 0);
            }

            // Priority ordering: featured/trending items first, then latest
            $products = $query->orderBy('is_featured', 'desc')
                ->orderBy('id', 'desc')
                ->take($limit)
                ->get();

            $formatted = $products->map(function ($product, $index) {
                $imageUrl = \App\Helpers\ImageHelper::resolve($product->main_image);
                $galleryUrls = \App\Helpers\ImageHelper::resolveGallery($product->gallery, $product->main_image);

                $mrp = (float) $product->mrp;
                $bookingPct = (float) ($product->booking_percentage ?? 20.00);
                $bookingAmount = (float) ($product->booking_amount > 0 ? $product->booking_amount : ($mrp * ($bookingPct / 100)));
                $balanceAmount = (float) ($product->balance_amount > 0 ? $product->balance_amount : ($mrp - $bookingAmount));
                $eligibleVal = (float) ($product->eligible_referral_value ?: $mrp);

                return [
                    'id'                      => $product->id,
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
                    'balance_due_days'        => 60,
                    'is_trending'             => true,
                    'deal_type'               => ($index % 2 === 0) ? 'Lightning Deal' : 'Trending Choice',
                    'deal_badge'              => ($index % 2 === 0) ? '⚡ LIGHTNING DEAL' : '🔥 TRENDING NOW',
                    'deal_tag'                => $product->offer_text ?: '20% Booking Advance Special',
                    'stock'                   => (int) $product->stock,
                    'in_stock'                => (int) $product->stock > 0,
                    'stock_status'            => (int) $product->stock > 0 ? 'In Stock' : 'Out of Stock',
                    'main_image'              => $imageUrl,
                    'imageUrl'                => $imageUrl,
                    'images'                  => $galleryUrls,
                    'gallery'                 => $galleryUrls,
                    'overview'                => $product->overview ?? $product->description,
                    'description'             => $product->overview ?? $product->description,
                    'features'                => is_array($product->key_features) ? $product->key_features : [],
                    'key_features'            => is_array($product->key_features) ? $product->key_features : [],
                    'referral_eligible'       => (bool) $product->referral_eligible,
                    'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
                    'eligible_referral_value' => $eligibleVal,
                    'self_dealer_benefit'     => [
                        'activates_self_dealer'   => (bool) $product->self_dealer_eligible,
                        'activation_points_pct'   => 20.00,
                        'activation_points_value' => $eligibleVal * 0.20,
                    ],
                    'is_featured'             => (bool) $product->is_featured,
                    'status'                  => $product->status,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Trending and lightning deal products retrieved successfully.',
                'total'   => $formatted->count(),
                'limit'   => $limit,
                'data'    => $formatted,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve trending products.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
