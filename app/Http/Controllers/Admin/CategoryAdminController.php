<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryAdminController extends Controller
{
    public function index()
    {
        $categories = Category::where('type', '!=', 'dls_farm_equipment')
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description'           => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'category_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $fileName);
            $imagePath = 'uploads/categories/' . $fileName;
        }

        // Auto-generate internal type from the category name (defaulting to clean slug)
        $lowerName = strtolower($request->name);
        if (str_contains($lowerName, 'scooter') || str_contains($lowerName, 'electric') || str_contains($lowerName, 'ev')) {
            $type = 'electric_mobility';
        } else {
            $slugged = Str::slug($request->name, '_');
            $type = !empty($slugged) && $slugged !== 'dls_farm_equipment' ? $slugged : 'general';
        }

        Category::create([
            'name'                   => $request->name,
            'slug'                   => Str::slug($request->name),
            'type'                   => $type,
            'referral_category_code' => $request->referral_category_code ? strtoupper($request->referral_category_code) : null,
            'referral_eligible'      => $request->has('referral_eligible') ? (bool)$request->referral_eligible : true,
            'image'                  => $imagePath,
            'description'            => $request->description,
            'is_active'              => true,
            'sort_order'             => Category::count() + 1,
        ]);

        return back()->with('success', 'Category added successfully!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'                  => 'required|string|max:255',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description'           => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'category_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $fileName);
            $category->image = 'uploads/categories/' . $fileName;
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if (empty($category->type)) {
            $category->type = Str::slug($request->name, '_');
        }
        if ($request->has('referral_category_code')) {
            $category->referral_category_code = $request->referral_category_code ? strtoupper($request->referral_category_code) : null;
        }
        if ($request->has('referral_eligible')) {
            $category->referral_eligible = (bool)$request->referral_eligible;
        }
        $category->description = $request->description;
        $category->save();

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
}
