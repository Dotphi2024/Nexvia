<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use Illuminate\Http\Request;

class SubcategoryApiController extends Controller
{
    /**
     * GET/POST /api/subcategories or /api/customer/subcategories
     * List all subcategories with optional category filter, search, and type.
     */
    public function index(Request $request)
    {
        try {
            $bodyJson = json_decode($request->getContent(), true) ?? [];

            $categoryId = $request->query('category_id')
                ?? $request->query('cat_id')
                ?? $request->query('category')
                ?? $request->query('cat')
                ?? $request->input('category_id')
                ?? $request->input('cat_id')
                ?? $request->input('category')
                ?? $request->input('cat')
                ?? ($bodyJson['category_id'] ?? null)
                ?? ($bodyJson['cat_id'] ?? null);

            $search = $request->query('search')
                ?? $request->input('search')
                ?? ($bodyJson['search'] ?? null);

            $type = $request->query('type')
                ?? $request->input('type')
                ?? ($bodyJson['type'] ?? null);

            $query = Subcategory::with(['category'])->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])->where('is_active', true);

            // Filter by Category (ID or Slug)
            if (!empty($categoryId)) {
                $categoryId = trim((string)$categoryId);
                if (is_numeric($categoryId)) {
                    $query->where('category_id', $categoryId);
                } else {
                    $query->whereHas('category', function ($q) use ($categoryId) {
                        $q->where('slug', $categoryId)->orWhere('name', 'like', "%{$categoryId}%");
                    });
                }
            }

            // Filter by Category Type (e.g. dls_farm_equipment vs standard)
            if (!empty($type)) {
                $type = strtolower(trim((string)$type));
                if (in_array($type, ['dls_farm_equipment', 'dls_agro', 'agro', 'farm', 'dls'])) {
                    $query->whereHas('category', function ($q) {
                        $q->where('type', 'dls_farm_equipment');
                    });
                } elseif (in_array($type, ['standard', 'regular', 'general', 'main'])) {
                    $query->whereHas('category', function ($q) {
                        $q->where('type', '!=', 'dls_farm_equipment');
                    });
                } else {
                    $query->whereHas('category', function ($q) use ($type) {
                        $q->where('type', $type);
                    });
                }
            }

            // Filter by Search Keyword
            if (!empty($search)) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('category', function ($cQ) use ($search) {
                          $cQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $subcategories = $query->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($sub) {
                    $imageUrl = \App\Helpers\ImageHelper::resolve($sub->image);

                    return [
                        'id'             => $sub->id,
                        'name'           => $sub->name,
                        'slug'           => $sub->slug,
                        'image'          => $imageUrl,
                        'description'    => $sub->description,
                        'sort_order'     => (int)$sub->sort_order,
                        'products_count' => (int)$sub->products_count,
                        'category'       => $sub->category ? [
                            'id'                     => $sub->category->id,
                            'name'                   => $sub->category->name,
                            'slug'                   => $sub->category->slug,
                            'type'                   => $sub->category->type,
                            'referral_category_code' => $sub->category->referral_category_code,
                        ] : null,
                    ];
                });

            return response()->json([
                'status'        => true,
                'total'         => count($subcategories),
                'subcategories' => $subcategories,
                'data'          => $subcategories,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch subcategories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/categories/{idOrSlug}/subcategories
     * Fetch subcategories under a specific parent category.
     */
    public function byCategory(Request $request, $idOrSlug)
    {
        try {
            $category = Category::where('is_active', true)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug)
                          ->orWhere('referral_category_code', strtoupper($idOrSlug));
                    }
                })
                ->first();

            if (!$category) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            $subcategories = Subcategory::where('category_id', $category->id)
                ->where('is_active', true)
                ->withCount(['products' => function ($q) {
                    $q->where('status', 'active');
                }])
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($sub) use ($category) {
                    return [
                        'id'             => $sub->id,
                        'name'           => $sub->name,
                        'slug'           => $sub->slug,
                        'image'          => \App\Helpers\ImageHelper::resolve($sub->image),
                        'description'    => $sub->description,
                        'sort_order'     => (int)$sub->sort_order,
                        'products_count' => (int)$sub->products_count,
                        'category_id'    => $category->id,
                    ];
                });

            return response()->json([
                'status'        => true,
                'category'      => [
                    'id'                     => $category->id,
                    'name'                   => $category->name,
                    'slug'                   => $category->slug,
                    'type'                   => $category->type,
                    'referral_category_code' => $category->referral_category_code,
                ],
                'total'         => count($subcategories),
                'subcategories' => $subcategories,
                'data'          => $subcategories,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch subcategories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET/POST /api/subcategories/{idOrSlug}
     * Get single subcategory details along with its active products.
     */
    public function show(Request $request, $idOrSlug)
    {
        try {
            $subcategory = Subcategory::with(['category'])
                ->withCount(['products' => function ($q) {
                    $q->where('status', 'active');
                }])
                ->where('is_active', true)
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->first();

            if (!$subcategory) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Subcategory not found.',
                ], 404);
            }

            $products = Product::where('subcategory_id', $subcategory->id)
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($p) use ($subcategory) {
                    $imageUrl = \App\Helpers\ImageHelper::resolve($p->main_image);
                    $bookingPct = (float)($p->booking_percentage ?? 20.00);
                    $mrp = (float)$p->mrp;
                    $bookingAmt = (float)($p->booking_amount ?: ($mrp * ($bookingPct / 100)));
                    $balanceAmt = (float)($p->balance_amount ?: ($mrp - $bookingAmt));

                    return [
                        'id'                 => $p->id,
                        'name'               => $p->name,
                        'slug'               => $p->slug,
                        'model_code'         => $p->model_code,
                        'sku'                => $p->sku,
                        'mrp'                => $mrp,
                        'booking_percentage' => $bookingPct,
                        'booking_amount'     => $bookingAmt,
                        'balance_amount'     => $balanceAmt,
                        'main_image'         => $imageUrl,
                        'image'              => $imageUrl,
                        'is_featured'        => (bool)$p->is_featured,
                        'stock'              => (int)$p->stock,
                        'status'             => $p->status,
                    ];
                });

            return response()->json([
                'status'      => true,
                'subcategory' => [
                    'id'             => $subcategory->id,
                    'name'           => $subcategory->name,
                    'slug'           => $subcategory->slug,
                    'image'          => \App\Helpers\ImageHelper::resolve($subcategory->image),
                    'description'    => $subcategory->description,
                    'sort_order'     => (int)$subcategory->sort_order,
                    'products_count' => (int)$subcategory->products_count,
                    'category'       => $subcategory->category ? [
                        'id'                     => $subcategory->category->id,
                        'name'                   => $subcategory->category->name,
                        'slug'                   => $subcategory->category->slug,
                        'type'                   => $subcategory->category->type,
                        'referral_category_code' => $subcategory->category->referral_category_code,
                    ] : null,
                    'products'       => $products,
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch subcategory details: ' . $e->getMessage(),
            ], 500);
        }
    }
}
