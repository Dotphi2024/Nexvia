<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;

class DlsAgroApiController extends Controller
{
    public const TYPE = 'dls_farm_equipment';

    /**
     * GET/POST /api/dls-agro/categories
     * Retrieve all active DLS Agro / Farm Equipment categories.
     */
    public function categories(Request $request)
    {
        try {
            $query = Category::where('type', self::TYPE)
                ->where('is_active', true)
                ->withCount(['products' => function ($q) {
                    $q->where('status', 'active');
                }]);

            if ($search = $request->input('search')) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('referral_category_code', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            $categories = $query->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($cat) {
                    return [
                        'id'                     => $cat->id,
                        'name'                   => $cat->name,
                        'slug'                   => $cat->slug,
                        'type'                   => $cat->type,
                        'referral_category_code' => $cat->referral_category_code,
                        'referral_eligible'      => (bool) $cat->referral_eligible,
                        'commission_percentage'  => (float) $cat->commission_percentage,
                        'description'            => $cat->description,
                        'image'                  => ImageHelper::resolve($cat->image),
                        'image_url'              => ImageHelper::resolve($cat->image),
                        'sort_order'             => (int) $cat->sort_order,
                        'products_count'         => (int) $cat->products_count,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'DLS Agro categories retrieved successfully.',
                'count'   => $categories->count(),
                'data'    => $categories,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve DLS Agro categories.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET/POST /api/dls-agro/products
     * Retrieve paginated list of active DLS Agro / Farm Equipment products.
     * Supports: category_id, search, sortBy, page, limit
     */
    public function products(Request $request)
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
                ?? ($bodyJson['category_id'] ?? null)
                ?? ($bodyJson['cat_id'] ?? null);

            $search = $request->input('search')
                ?? ($bodyJson['search'] ?? null);

            $sortBy = $request->input('sortBy')
                ?? $request->input('sort_by')
                ?? ($bodyJson['sortBy'] ?? null)
                ?? ($bodyJson['sort_by'] ?? null);

            $page = (int) ($request->input('page') ?? ($bodyJson['page'] ?? 1));
            $perPage = (int) ($request->input('limit') ?? $request->input('per_page') ?? ($bodyJson['limit'] ?? null) ?? 10);
            $perPage = max(1, min(100, $perPage));

            $query = Product::whereHas('category', function ($q) {
                $q->where('type', self::TYPE);
            })->with('category')->where('status', 'active');

            // Category Filter
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

            // Search Keyword Filter
            if (!empty($search)) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model_code', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('offer_text', 'like', "%{$search}%");
                });
            }

            // Sorting
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
                case 'featured':
                case 'popular':
                case 'trending':
                    $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc');
                    break;
                default:
                    $query->orderBy('id', 'desc');
                    break;
            }

            $products = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedProducts = collect($products->items())->map(function ($product) {
                return $this->formatProductSummary($product);
            });

            return response()->json([
                'status'     => true,
                'message'    => 'DLS Agro products retrieved successfully.',
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
                'message' => 'Failed to retrieve DLS Agro products.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET/POST /api/dls-agro/products/featured
     * Retrieve featured / trending DLS Agro products.
     */
    public function featured(Request $request)
    {
        try {
            $limit = (int) ($request->input('limit') ?? 10);
            $limit = max(1, min(50, $limit));

            $products = Product::whereHas('category', function ($q) {
                $q->where('type', self::TYPE);
            })->with('category')
              ->where('status', 'active')
              ->where('is_featured', true)
              ->latest()
              ->take($limit)
              ->get()
              ->map(function ($product) {
                  return $this->formatProductSummary($product);
              });

            return response()->json([
                'status'  => true,
                'message' => 'Featured DLS Agro products retrieved successfully.',
                'count'   => $products->count(),
                'data'    => $products,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve featured DLS Agro products.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET/POST /api/dls-agro/products/{idOrSlug}
     * Retrieve full details of a specific DLS Agro product.
     */
    public function show(Request $request, $idOrSlug)
    {
        try {
            $product = Product::whereHas('category', function ($q) {
                $q->where('type', self::TYPE);
            })->with('category')
              ->where(function ($q) use ($idOrSlug) {
                  if (is_numeric($idOrSlug)) {
                      $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
                  } else {
                      $q->where('slug', $idOrSlug);
                  }
              })->first();

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'DLS Agro product not found.',
                ], 404);
            }

            $mainImageUrl = ImageHelper::resolve($product->main_image);
            $galleryUrls  = ImageHelper::resolveGallery($product->gallery, $product->main_image);

            $mrp = (float) $product->mrp;
            $bookingPct = (float) ($product->booking_percentage ?? 20.00);
            $bookingAmount = (float) ($product->booking_amount > 0 ? $product->booking_amount : ($mrp * ($bookingPct / 100)));
            $balanceAmount = (float) ($product->balance_amount > 0 ? $product->balance_amount : ($mrp - $bookingAmount));

            $eligibleValue = (float) ($product->eligible_referral_value ?: $mrp);

            $specsMap = $product->specs ?? [];
            if (is_string($specsMap)) {
                $specsMap = json_decode($specsMap, true) ?? [];
            }
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

            $stockQty = (int) $product->stock;

            return response()->json([
                'status'  => true,
                'message' => 'DLS Agro product details retrieved successfully.',
                'data'    => [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'slug'               => $product->slug,
                    'model_code'         => $product->model_code,
                    'sku'                => $product->sku,
                    'product_type'       => 'dls_farm_equipment',

                    'category'           => $product->category ? [
                        'id'                     => $product->category->id,
                        'name'                   => $product->category->name,
                        'slug'                   => $product->category->slug,
                        'type'                   => $product->category->type,
                        'referral_category_code' => $product->category->referral_category_code,
                    ] : null,

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
                        'pricing_summary'    => "Pay ₹" . number_format($bookingAmount, 2) . " ({$bookingPct}%) now, balance ₹" . number_format($balanceAmount, 2) . " within 60 days.",
                    ],

                    'referral_eligible'       => (bool) $product->referral_eligible,
                    'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
                    'eligible_referral_value' => $eligibleValue,

                    'stock'              => $stockQty,
                    'in_stock'           => $stockQty > 0,
                    'stock_status'       => $stockQty > 0 ? 'In Stock' : 'Out of Stock',

                    'main_image'         => $mainImageUrl,
                    'imageUrl'           => $mainImageUrl,
                    'gallery'            => $galleryUrls,
                    'images'             => $galleryUrls,
                    'video_url'          => $product->video_url,

                    'offer_text'         => $product->offer_text,
                    'key_features'       => $keyFeatures,
                    'specifications'     => $specificationsList,
                    'specs'              => $specsMap,

                    'warranty_info'      => $product->warranty_info,
                    'installation_info'  => $product->installation_info,
                    'delivery_info'      => $product->delivery_info,
                    'is_featured'        => (bool) $product->is_featured,
                    'status'             => $product->status,
                    'created_at'         => $product->created_at ? $product->created_at->toIso8601String() : null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve DLS Agro product details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to format a product summary item.
     */
    protected function formatProductSummary(Product $product): array
    {
        $imageUrl = ImageHelper::resolve($product->main_image);
        $galleryUrls = ImageHelper::resolveGallery($product->gallery, $product->main_image);

        return [
            'id'                 => $product->id,
            'name'               => $product->name,
            'slug'               => $product->slug,
            'model_code'         => $product->model_code,
            'sku'                => $product->sku,
            'product_type'       => 'dls_farm_equipment',
            'category'           => $product->category ? [
                'id'                     => $product->category->id,
                'name'                   => $product->category->name,
                'slug'                   => $product->category->slug,
                'type'                   => $product->category->type,
                'referral_category_code' => $product->category->referral_category_code,
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
            'warranty_info'      => $product->warranty_info,
            'installation_info'  => $product->installation_info,
            'is_featured'        => (bool) $product->is_featured,
            'status'             => $product->status,
            'eligible_referral_value' => (float) ($product->eligible_referral_value ?: $product->mrp),
            'referral_eligible'       => (bool) $product->referral_eligible,
            'self_dealer_eligible'    => (bool) $product->self_dealer_eligible,
            'created_at'         => $product->created_at ? $product->created_at->toIso8601String() : null,
        ];
    }
}
