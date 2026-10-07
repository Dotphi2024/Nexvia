<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DlsFarmBannerAdminController extends Controller
{
    public const SECTION = 'dls_farm_equipment';
    public const TYPE = 'dls_farm_equipment';

    /**
     * Display listing of DLS Agro / Farm Equipment banners with KPIs.
     */
    public function index(Request $request)
    {
        $query = Banner::where('section', self::SECTION);

        // Filter by banner type / position
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'side') {
                $query->whereIn('banner_type', ['side', 'side_banner']);
            } else {
                $query->where('banner_type', $type);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $banners = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        // KPI Counters for Agro Section
        $totalBanners  = Banner::where('section', self::SECTION)->count();
        $activeBanners = Banner::where('section', self::SECTION)->where('is_active', true)->count();
        $heroBanners   = Banner::where('section', self::SECTION)->where('banner_type', 'hero')->count();
        $promoBanners  = Banner::where('section', self::SECTION)->where('banner_type', 'promo')->count();
        $sideBanners   = Banner::where('section', self::SECTION)->whereIn('banner_type', ['side', 'side_banner'])->count();
        $totalClicks   = Banner::where('section', self::SECTION)->sum('clicks_count');

        // Only DLS Agro / Farm Equipment categories and products
        $categories = Category::where('is_active', true)
            ->where('type', self::TYPE)
            ->orderBy('name')
            ->get();

        $products = Product::where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->where('type', self::TYPE);
            })
            ->orderBy('name')
            ->get();

        return view('admin.dls_farm_equipments.banners.index', compact(
            'banners',
            'totalBanners',
            'activeBanners',
            'heroBanners',
            'promoBanners',
            'sideBanners',
            'totalClicks',
            'categories',
            'products'
        ));
    }

    /**
     * Store a newly created Agro banner.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:100',
            'banner_type'  => 'required|string|in:hero,promo,side,side_banner,popup,referral',
            'target_type'  => 'required|string|in:url,category,product,booking,none',
            'target_id'    => 'nullable|integer',
            'link_url'     => 'nullable|string|max:500',
            'button_text'  => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer',
            'image'        => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads/banners/agro');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'agro_banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $imagePath = 'uploads/banners/agro/' . $fileName;
        }

        $sortOrder = $request->filled('sort_order')
            ? (int) $request->sort_order
            : (Banner::where('section', self::SECTION)->max('sort_order') + 1);

        Banner::create([
            'title'        => $request->title,
            'subtitle'     => $request->subtitle,
            'badge_text'   => $request->badge_text,
            'banner_type'  => $request->banner_type,
            'section'      => self::SECTION,
            'position'     => $request->banner_type,
            'target_type'  => $request->target_type,
            'target_id'    => $request->target_id ?: null,
            'link_url'     => $request->link_url,
            'button_text'  => $request->button_text ?: 'Shop Agro Products',
            'sort_order'   => $sortOrder,
            'is_active'    => $request->has('is_active') ? (bool) $request->is_active : true,
            'image'        => $imagePath,
            'start_date'   => $request->start_date ?: null,
            'end_date'     => $request->end_date ?: null,
        ]);

        return redirect()->route('admin.dls_farm_equipments.banners.index')
            ->with('success', 'DLS Agro Banner created successfully!');
    }

    /**
     * Get banner data for edit (used by modal or API).
     */
    public function edit($id)
    {
        $banner = Banner::where('section', self::SECTION)->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'data'   => $banner,
            ]);
        }

        $categories = Category::where('is_active', true)->where('type', self::TYPE)->orderBy('name')->get();
        $products   = Product::where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->where('type', self::TYPE);
            })
            ->orderBy('name')
            ->get();

        return view('admin.dls_farm_equipments.banners.edit', compact('banner', 'categories', 'products'));
    }

    /**
     * Update an existing Agro banner.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::where('section', self::SECTION)->findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:100',
            'banner_type'  => 'required|string|in:hero,promo,side,side_banner,popup,referral',
            'target_type'  => 'required|string|in:url,category,product,booking,none',
            'target_id'    => 'nullable|integer',
            'link_url'     => 'nullable|string|max:500',
            'button_text'  => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $data = [
            'title'        => $request->title,
            'subtitle'     => $request->subtitle,
            'badge_text'   => $request->badge_text,
            'banner_type'  => $request->banner_type,
            'section'      => self::SECTION,
            'position'     => $request->banner_type,
            'target_type'  => $request->target_type,
            'target_id'    => $request->target_id ?: null,
            'link_url'     => $request->link_url,
            'button_text'  => $request->button_text ?: 'Shop Agro Products',
            'sort_order'   => $request->filled('sort_order') ? (int) $request->sort_order : $banner->sort_order,
            'is_active'    => $request->has('is_active') ? (bool) $request->is_active : false,
            'start_date'   => $request->start_date ?: null,
            'end_date'     => $request->end_date ?: null,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads/banners/agro');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'agro_banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['image'] = 'uploads/banners/agro/' . $fileName;

            if ($banner->image && file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            }
        }

        $banner->update($data);

        return redirect()->route('admin.dls_farm_equipments.banners.index')
            ->with('success', 'DLS Agro Banner updated successfully!');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $banner = Banner::where('section', self::SECTION)->findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'    => true,
                'is_active' => $banner->is_active,
                'message'   => 'Agro banner status updated to ' . ($banner->is_active ? 'Active' : 'Inactive'),
            ]);
        }

        return redirect()->back()->with('success', 'Banner status updated successfully!');
    }

    /**
     * Delete banner and file.
     */
    public function destroy($id)
    {
        $banner = Banner::where('section', self::SECTION)->findOrFail($id);

        if ($banner->image && file_exists(public_path($banner->image))) {
            @unlink(public_path($banner->image));
        }

        $banner->delete();

        return redirect()->route('admin.dls_farm_equipments.banners.index')
            ->with('success', 'DLS Agro Banner deleted successfully!');
    }
}
