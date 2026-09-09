<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BannerAdminController extends Controller
{
    /**
     * Display listing of banners with metrics and create/edit resources.
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // Optional filter by banner type
        if ($request->filled('type')) {
            $query->where('banner_type', $request->type);
        }

        // Optional filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $banners = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        // Quick KPI counters
        $totalBanners  = Banner::count();
        $activeBanners = Banner::where('is_active', true)->count();
        $heroBanners   = Banner::where('banner_type', 'hero')->count();
        $promoBanners  = Banner::where('banner_type', 'promo')->count();
        $totalClicks   = Banner::sum('clicks_count');

        // Categories and Products for target selections
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products   = Product::where('status', 'active')->orderBy('name')->get();

        return view('admin.banners.index', compact(
            'banners',
            'totalBanners',
            'activeBanners',
            'heroBanners',
            'promoBanners',
            'totalClicks',
            'categories',
            'products'
        ));
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:100',
            'banner_type'  => 'required|string|in:hero,promo,referral,popup',
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
            $uploadDir = public_path('uploads/banners');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $imagePath = 'uploads/banners/' . $fileName;
        }

        $sortOrder = $request->filled('sort_order')
            ? (int) $request->sort_order
            : (Banner::max('sort_order') + 1);

        Banner::create([
            'title'        => $request->title,
            'subtitle'     => $request->subtitle,
            'badge_text'   => $request->badge_text,
            'banner_type'  => $request->banner_type,
            'target_type'  => $request->target_type,
            'target_id'    => $request->target_id ?: null,
            'link_url'     => $request->link_url,
            'button_text'  => $request->button_text ?: 'Shop Now',
            'sort_order'   => $sortOrder,
            'is_active'    => $request->has('is_active') ? (bool) $request->is_active : true,
            'image'        => $imagePath,
            'start_date'   => $request->start_date ?: null,
            'end_date'     => $request->end_date ?: null,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully!');
    }

    /**
     * Get banner data for edit (used by modal or API).
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'data'   => $banner,
            ]);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products   = Product::where('status', 'active')->orderBy('name')->get();

        return view('admin.banners.edit', compact('banner', 'categories', 'products'));
    }

    /**
     * Update an existing banner.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:100',
            'banner_type'  => 'required|string|in:hero,promo,referral,popup',
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
            'target_type'  => $request->target_type,
            'target_id'    => $request->target_id ?: null,
            'link_url'     => $request->link_url,
            'button_text'  => $request->button_text ?: 'Shop Now',
            'sort_order'   => $request->filled('sort_order') ? (int) $request->sort_order : $banner->sort_order,
            'is_active'    => $request->has('is_active') ? (bool) $request->is_active : false,
            'start_date'   => $request->start_date ?: null,
            'end_date'     => $request->end_date ?: null,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads/banners');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'banner_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $data['image'] = 'uploads/banners/' . $fileName;

            // Remove old image if local
            if ($banner->image && file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            }
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully!');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'    => true,
                'is_active' => $banner->is_active,
                'message'   => 'Banner status updated to ' . ($banner->is_active ? 'Active' : 'Inactive'),
            ]);
        }

        return redirect()->back()->with('success', 'Banner status updated successfully!');
    }

    /**
     * Delete banner and file.
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image && file_exists(public_path($banner->image))) {
            @unlink(public_path($banner->image));
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully!');
    }
}
