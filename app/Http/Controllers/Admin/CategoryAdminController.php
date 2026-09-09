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
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'category_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $fileName);
            $imagePath = 'uploads/categories/' . $fileName;
        }

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'referral_category_code' => $request->referral_category_code ? strtoupper($request->referral_category_code) : null,
            'referral_eligible' => $request->has('referral_eligible'),
            'commission_percentage' => $request->commission_percentage,
            'image' => $imagePath,
            'description' => $request->description,
            'is_active' => true,
            'sort_order' => Category::count() + 1,
        ]);

        return back()->with('success', 'Category added successfully with image upload!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'                  => 'required|string|max:255',
            'type'                  => 'required|string',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
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
        $category->type = $request->type;
        $category->referral_category_code = $request->referral_category_code ? strtoupper($request->referral_category_code) : null;
        $category->referral_eligible = $request->has('referral_eligible');
        if ($request->filled('commission_percentage')) {
            $category->commission_percentage = $request->commission_percentage;
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
