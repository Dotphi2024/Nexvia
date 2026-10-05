<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DlsFarmSubcategoryAdminController extends Controller
{
    public const TYPE = 'dls_farm_equipment';

    /**
     * Display a listing of farm equipment subcategories.
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $search = trim((string)$request->query('search', $request->query('q')));

        $query = Subcategory::whereHas('category', function ($q) {
            $q->where('type', self::TYPE);
        })->with('category')->withCount(['products' => function ($pQ) {
            $pQ->whereHas('category', function ($cQ) {
                $cQ->where('type', self::TYPE);
            });
        }]);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cQ) use ($search) {
                      $cQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $subcategories = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $categories = Category::where('type', self::TYPE)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.dls_farm_equipments.subcategories.index', compact('subcategories', 'categories', 'categoryId', 'search'));
    }

    /**
     * Store a newly created farm equipment subcategory.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        // Ensure category is indeed a farm equipment category
        $category = Category::where('type', self::TYPE)->findOrFail($request->category_id);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'farm_subcat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/subcategories'), $fileName);
            $imagePath = 'uploads/subcategories/' . $fileName;
        }

        $slug = Str::slug($request->name);
        $exists = Subcategory::where('category_id', $category->id)->where('slug', $slug)->exists();
        if ($exists) {
            $slug .= '-' . rand(10, 99);
        }

        $nextSort = (Subcategory::where('category_id', $category->id)->max('sort_order') ?? 0) + 1;

        Subcategory::create([
            'category_id' => $category->id,
            'name'        => $request->name,
            'slug'        => $slug,
            'image'       => $imagePath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? (bool)$request->is_active : true,
            'sort_order'  => (int)($request->sort_order ?: $nextSort),
        ]);

        return back()->with('success', 'DLS Farm Equipment subcategory created successfully!');
    }

    /**
     * Update the specified farm equipment subcategory.
     */
    public function update(Request $request, $id)
    {
        $subcategory = Subcategory::whereHas('category', function ($q) {
            $q->where('type', self::TYPE);
        })->findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        $category = Category::where('type', self::TYPE)->findOrFail($request->category_id);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'farm_subcat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/subcategories'), $fileName);
            $subcategory->image = 'uploads/subcategories/' . $fileName;
        }

        $subcategory->category_id = $category->id;
        $subcategory->name = $request->name;
        $subcategory->slug = Str::slug($request->name);
        $subcategory->description = $request->description;
        if ($request->has('is_active')) {
            $subcategory->is_active = (bool)$request->is_active;
        }
        if ($request->filled('sort_order')) {
            $subcategory->sort_order = (int)$request->sort_order;
        }
        $subcategory->save();

        return back()->with('success', 'DLS Farm Equipment subcategory updated successfully!');
    }

    /**
     * Remove the specified farm equipment subcategory.
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::whereHas('category', function ($q) {
            $q->where('type', self::TYPE);
        })->findOrFail($id);

        $subcategory->delete();
        return back()->with('success', 'DLS Farm Equipment subcategory deleted successfully!');
    }

    /**
     * AJAX endpoint: Get subcategories by category ID for farm equipments.
     */
    public function byCategory(Request $request)
    {
        $categoryId = $request->query('category_id', $request->input('category_id'));
        if (empty($categoryId)) {
            return response()->json(['status' => true, 'subcategories' => []]);
        }

        $subcategories = Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'slug', 'image', 'sort_order']);

        return response()->json([
            'status'        => true,
            'subcategories' => $subcategories,
        ]);
    }
}
