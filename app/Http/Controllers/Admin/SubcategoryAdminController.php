<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryAdminController extends Controller
{
    /**
     * Display a listing of subcategories.
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $search = trim((string)$request->query('search', $request->query('q')));

        $query = Subcategory::with('category')->withCount('products');

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
        $categories = Category::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('admin.subcategories.index', compact('subcategories', 'categories', 'categoryId', 'search'));
    }

    /**
     * Store a newly created subcategory in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'subcat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/subcategories'), $fileName);
            $imagePath = 'uploads/subcategories/' . $fileName;
        }

        $slug = Str::slug($request->name);
        $exists = Subcategory::where('category_id', $request->category_id)->where('slug', $slug)->exists();
        if ($exists) {
            $slug .= '-' . rand(10, 99);
        }

        $nextSort = (Subcategory::where('category_id', $request->category_id)->max('sort_order') ?? 0) + 1;

        Subcategory::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'image'       => $imagePath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? (bool)$request->is_active : true,
            'sort_order'  => (int)($request->sort_order ?: $nextSort),
        ]);

        return back()->with('success', 'Subcategory created successfully!');
    }

    /**
     * Update the specified subcategory in storage.
     */
    public function update(Request $request, $id)
    {
        $subcategory = Subcategory::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'subcat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/subcategories'), $fileName);
            $subcategory->image = 'uploads/subcategories/' . $fileName;
        }

        $subcategory->category_id = $request->category_id;
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

        return back()->with('success', 'Subcategory updated successfully!');
    }

    /**
     * Remove the specified subcategory from storage.
     */
    public function destroy($id)
    {
        $subcategory = Subcategory::findOrFail($id);
        $subcategory->delete();
        return back()->with('success', 'Subcategory deleted successfully!');
    }

    /**
     * AJAX endpoint: Get subcategories by category ID.
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
