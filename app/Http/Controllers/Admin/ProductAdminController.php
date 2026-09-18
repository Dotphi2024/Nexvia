<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $categoryId = $request->query('category_id');
        $search = trim((string)$request->query('q'));

        $query = Product::whereHas('category', function ($q) {
            $q->where('type', '!=', 'dls_farm_equipment');
        })->with('category');

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_code', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($filter === 'trending') {
            $query->where('is_featured', true);
        } elseif ($filter === 'inactive') {
            $query->where('status', 'inactive');
        } elseif ($filter === 'instock') {
            $query->where('stock', '>', 0);
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('type', '!=', 'dls_farm_equipment')->orderBy('name')->get();

        $baseCountQuery = Product::whereHas('category', function ($q) {
            $q->where('type', '!=', 'dls_farm_equipment');
        });

        $totalCount = (clone $baseCountQuery)->count();
        $trendingCount = (clone $baseCountQuery)->where('is_featured', true)->count();
        $inactiveCount = (clone $baseCountQuery)->where('status', 'inactive')->count();

        return view('admin.products.index', compact('products', 'categories', 'filter', 'categoryId', 'search', 'totalCount', 'trendingCount', 'inactiveCount'));
    }

    public function create()
    {
        $categories = Category::where('type', '!=', 'dls_farm_equipment')->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'model_code' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'mrp' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'video_url' => 'nullable|url',
            'offer_text' => 'nullable|string|max:255',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $mrp = $request->mrp;
        $bookingPercentage = $request->booking_percentage ?? 20;
        $bookingAmount = $mrp * ($bookingPercentage / 100);
        $balanceAmount = $mrp - $bookingAmount;

        $mainImagePath = null;
        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $fileName = 'product_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $fileName);
            $mainImagePath = 'uploads/products/' . $fileName;
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $gFile) {
                $gFileName = 'product_g_' . time() . '_' . Str::random(6) . '.' . $gFile->getClientOriginalExtension();
                $gFile->move(public_path('uploads/products'), $gFileName);
                $galleryPaths[] = 'uploads/products/' . $gFileName;
            }
        }

        $eligibleReferralValue = $request->eligible_referral_value ?: $mrp;
        $referralEligible = $request->has('referral_eligible');
        $selfDealerEligible = $request->has('self_dealer_eligible');

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'model_code' => $request->model_code,
            'sku' => $request->sku,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'mrp' => $mrp,
            'booking_percentage' => $bookingPercentage,
            'booking_amount' => $bookingAmount,
            'balance_amount' => $balanceAmount,
            'eligible_referral_value' => $eligibleReferralValue,
            'referral_eligible' => $referralEligible,
            'self_dealer_eligible' => $selfDealerEligible,
            'stock' => $request->stock,
            'video_url' => $request->video_url,
            'offer_text' => $request->offer_text,
            'main_image' => $mainImagePath,
            'gallery' => $galleryPaths,
            'warranty_info' => $request->warranty_info ?? '1 Year Brand Warranty',
            'installation_info' => $request->installation_info ?? 'Free Installation Available',
            'is_featured' => $request->has('is_featured'),
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('type', '!=', 'dls_farm_equipment')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'mrp' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'video_url' => 'nullable|url',
        ]);

        $mrp = $request->mrp;
        $bookingPercentage = $request->booking_percentage ?? 20;
        $bookingAmount = $mrp * ($bookingPercentage / 100);
        $balanceAmount = $mrp - $bookingAmount;
        $eligibleReferralValue = $request->eligible_referral_value ?: $mrp;

        $mainImagePath = $product->main_image;
        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $fileName = 'product_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $fileName);
            $mainImagePath = 'uploads/products/' . $fileName;
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'model_code' => $request->model_code,
            'sku' => $request->sku,
            'mrp' => $mrp,
            'booking_percentage' => $bookingPercentage,
            'booking_amount' => $bookingAmount,
            'balance_amount' => $balanceAmount,
            'eligible_referral_value' => $eligibleReferralValue,
            'referral_eligible' => $request->has('referral_eligible'),
            'self_dealer_eligible' => $request->has('self_dealer_eligible'),
            'stock' => $request->stock,
            'video_url' => $request->video_url,
            'offer_text' => $request->offer_text,
            'main_image' => $mainImagePath,
            'warranty_info' => $request->warranty_info,
            'is_featured' => $request->has('is_featured'),
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = ($product->status === 'active') ? 'inactive' : 'active';
        $product->save();

        return back()->with('success', "Product status changed to {$product->status}!");
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        $statusText = $product->is_featured ? 'marked as 🔥 Trending & Lightning Deal' : 'removed from Trending';
        return back()->with('success', "Product '{$product->name}' has been {$statusText}!");
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return back()->with('success', 'Product deleted successfully!');
    }
}
