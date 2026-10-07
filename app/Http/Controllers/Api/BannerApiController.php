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

            // Filter by section (default: 'home' unless explicitly requested)
            $section = $request->input('section');
            if ($section === 'all') {
                // Return all banners across home and agro
            } elseif (in_array($section, ['dls_farm_equipment', 'dls_agro', 'agro', 'farm'])) {
                $query->where('section', 'dls_farm_equipment');
            } else {
                $query->home();
            }

            // Filter by position or type
            if ($request->filled('type') || $request->filled('position')) {
                $pos = $request->input('type') ?? $request->input('position');
                if ($pos === 'side') {
                    $query->where(function ($q) {
                        $q->where('banner_type', 'side')
                          ->orWhere('banner_type', 'side_banner')
                          ->orWhere('position', 'side')
                          ->orWhere('position', 'side_banner');
                    });
                } else {
                    $query->where(function ($q) use ($pos) {
                        $q->where('banner_type', $pos)
                          ->orWhere('position', $pos);
                    });
                }
            }

            $allBanners = $query->get()->map(function ($banner) {
                return [
                    'id'           => $banner->id,
                    'title'        => $banner->title,
                    'subtitle'     => $banner->subtitle,
                    'badge_text'   => $banner->badge_text,
                    'position'     => $banner->banner_type,
                    'banner_type'  => $banner->banner_type,
                    'section'      => $banner->section ?: 'home',
                    'target_type'  => $banner->target_type,
                    'target_id'    => $banner->target_id,
                    'link_url'     => $banner->link_url,
                    'button_text'  => $banner->button_text,
                    'image_url'    => $banner->image_url,
                    'sort_order'   => (int) $banner->sort_order,
                    'clicks_count' => (int) $banner->clicks_count,
                ];
            });

            // Group into layout components
            $heroSliders  = $allBanners->where('banner_type', 'hero')->values();
            $promoBanners = $allBanners->where('banner_type', 'promo')->values();
            $sideBanners  = $allBanners->filter(function ($b) {
                return in_array($b['banner_type'], ['side', 'side_banner']) || in_array($b['position'], ['side', 'side_banner']);
            })->values();
            $popupBanners = $allBanners->where('banner_type', 'popup')->values();

            return response()->json([
                'status'       => true,
                'message'      => 'Banners retrieved successfully.',
                'total'        => $allBanners->count(),
                'home_section' => [
                    'hero_sliders'  => $heroSliders,
                    'promo_banners' => $promoBanners,
                    'side_banners'  => $sideBanners,
                    'popup_banners' => $popupBanners,
                ],
                'data'         => $allBanners,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve banners.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/banners/side or /api/home/banners/side
     * Retrieve active side banners for web/app sidebars.
     */
    public function sideBanners(Request $request)
    {
        $request->merge(['position' => 'side']);
        return $this->index($request);
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
