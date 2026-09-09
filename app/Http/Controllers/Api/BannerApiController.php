<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerApiController extends Controller
{
    /**
     * GET /api/home/banners or /api/banners
     * Fetch active banners specifically for Home Section / Home Index Page.
     * Returns top hero sliders and middle promo banners.
     */
    public function index(Request $request)
    {
        try {
            $query = Banner::active()->ordered();

            if ($request->filled('type') || $request->filled('position')) {
                $pos = $request->input('type') ?? $request->input('position');
                $query->where('banner_type', $pos);
            }

            $allBanners = $query->get()->map(function ($banner) {
                return [
                    'id'          => $banner->id,
                    'title'       => $banner->title,
                    'subtitle'    => $banner->subtitle,
                    'badge_text'  => $banner->badge_text,
                    'position'    => $banner->banner_type, // 'hero' (top slider) or 'promo' (middle banner)
                    'banner_type' => $banner->banner_type,
                    'target_type' => $banner->target_type,
                    'target_id'   => $banner->target_id,
                    'link_url'    => $banner->link_url,
                    'button_text' => $banner->button_text,
                    'image_url'   => $banner->image_url,
                    'sort_order'  => (int) $banner->sort_order,
                    'clicks_count'=> (int) $banner->clicks_count,
                ];
            });

            // Group into home section components
            $heroSliders  = $allBanners->where('banner_type', 'hero')->values();
            $promoBanners = $allBanners->where('banner_type', 'promo')->values();

            return response()->json([
                'status'       => true,
                'message'      => 'Home section banners retrieved successfully.',
                'total'        => $allBanners->count(),
                'home_section' => [
                    'hero_sliders'  => $heroSliders,
                    'promo_banners' => $promoBanners,
                ],
                'data'         => $allBanners,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve home banners.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/banners/{id}/click
     * Record a click/tap on a home banner for analytics.
     */
    public function recordClick($id)
    {
        try {
            $banner = Banner::find($id);
            if ($banner) {
                $banner->increment('clicks_count');
            }

            return response()->json([
                'status'  => true,
                'message' => 'Click recorded.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error recording click.'], 500);
        }
    }
}
