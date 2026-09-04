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

            $categoryInput = $request->input('category')
                ?? $request->input('category_id')
                ?? ($bodyJson['category'] ?? null)
                ?? ($bodyJson['category_id'] ?? null);

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
                $imageUrl = $product->main_image
                    ? (str_starts_with($product->main_image, 'http') ? $product->main_image : asset($product->main_image))
                    : null;

                return [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'slug'               => $product->slug,
                    'model_code'         => $product->model_code,
                    'sku'                => $product->sku,
                    'category'           => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'mrp'                => (float) $product->mrp,
                    'booking_percentage' => (float) ($product->booking_percentage ?? 20.00),
                    'booking_amount'     => (float) $product->booking_amount,
                    'balance_amount'     => (float) $product->balance_amount,
                    'stock'              => (int) $product->stock,
                    'main_image'         => $imageUrl,
                    'imageUrl'           => $imageUrl,
                    'video_url'          => $product->video_url,
                    'offer_text'         => $product->offer_text,
                    'is_featured'        => (bool) $product->is_featured,
                    'status'             => $product->status,
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
     * GET /api/products/{id_or_slug}
     * Get single product detail
     */
    public function show($idOrSlug)
    {
        try {
            $product = Product::with('category')
                ->where('status', 'active')
                ->where(function ($q) use ($idOrSlug) {
                    if (is_numeric($idOrSlug)) {
                        $q->where('id', $idOrSlug);
                    } else {
                        $q->where('slug', $idOrSlug);
                    }
                })
                ->first();

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            $imageUrl = $product->main_image
                ? (str_starts_with($product->main_image, 'http') ? $product->main_image : asset($product->main_image))
                : null;

            return response()->json([
                'status'  => true,
                'message' => 'Product detail retrieved successfully.',
                'data'    => [
                    'id'                 => $product->id,
                    'name'               => $product->name,
                    'slug'               => $product->slug,
                    'model_code'         => $product->model_code,
                    'sku'                => $product->sku,
                    'category'           => $product->category ? [
                        'id'   => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'mrp'                => (float) $product->mrp,
                    'booking_percentage' => (float) ($product->booking_percentage ?? 20.00),
                    'booking_amount'     => (float) $product->booking_amount,
                    'balance_amount'     => (float) $product->balance_amount,
                    'stock'              => (int) $product->stock,
                    'main_image'         => $imageUrl,
                    'imageUrl'           => $imageUrl,
                    'video_url'          => $product->video_url,
                    'offer_text'         => $product->offer_text,
                    'gallery'            => $product->gallery ?? [],
                    'key_features'       => $product->key_features ?? [],
                    'specs'              => $product->specs ?? [],
                    'warranty_info'      => $product->warranty_info,
                    'installation_info'  => $product->installation_info,
                    'delivery_info'      => $product->delivery_info,
                    'is_featured'        => (bool) $product->is_featured,
                    'status'             => $product->status,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve product detail.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
