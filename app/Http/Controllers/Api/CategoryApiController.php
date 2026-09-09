<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /**
     * GET /api/categories or /api/customer/categories
     * Get category list with product counts
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::withCount('products')
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($cat) {
                    $imageUrl = $cat->image
                        ? (str_starts_with($cat->image, 'http') ? $cat->image : asset($cat->image))
                        : null;

                    return [
                        'id'                     => $cat->id,
                        'name'                   => $cat->name,
                        'slug'                   => $cat->slug,
                        'type'                   => $cat->type,
                        'referral_category_code' => $cat->referral_category_code,
                        'referral_eligible'      => (bool) $cat->referral_eligible,
                        'description'            => $cat->description,
                        'imageUrl'               => $imageUrl,
                        'products_count'         => $cat->products_count,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Categories retrieved successfully.',
                'total'   => $categories->count(),
                'data'    => $categories,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve categories.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
