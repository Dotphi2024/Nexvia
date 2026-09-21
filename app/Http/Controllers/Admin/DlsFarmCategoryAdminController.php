<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DlsFarmCategoryAdminController extends Controller
{
    public const TYPE = 'dls_farm_equipment';

    public function index()
    {
        $categories = Category::where('type', self::TYPE)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('admin.dls_farm_equipments.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description'           => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'farm_cat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $fileName);
            $imagePath = 'uploads/categories/' . $fileName;
        }

        Category::create([
            'name'                   => $request->name,
            'slug'                   => Str::slug($request->name) . '-' . rand(100, 999),
            'type'                   => self::TYPE,
            'referral_category_code' => $request->referral_category_code ? strtoupper($request->referral_category_code) : null,
            'referral_eligible'      => $request->has('referral_eligible'),
            'commission_percentage'  => $request->filled('commission_percentage') ? (float) $request->commission_percentage : 5.00,
            'image'                  => $imagePath,
            'description'            => $request->description,
            'is_active'              => true,
            'sort_order'             => Category::where('type', self::TYPE)->count() + 1,
        ]);

        return back()->with('success', 'DLS Farm Equipment category added successfully!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('type', self::TYPE)->findOrFail($id);

        $request->validate([
            'name'                  => 'required|string|max:255',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description'           => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'farm_cat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $fileName);
            $category->image = 'uploads/categories/' . $fileName;
        }

        $category->name = $request->name;
        $category->referral_category_code = $request->referral_category_code ? strtoupper($request->referral_category_code) : null;
        $category->referral_eligible = $request->has('referral_eligible');
        if ($request->filled('commission_percentage')) {
            $category->commission_percentage = (float) $request->commission_percentage;
        }
        $category->description = $request->description;
        $category->save();

        return back()->with('success', 'DLS Farm Equipment category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::where('type', self::TYPE)->findOrFail($id);
        $category->delete();
        return back()->with('success', 'DLS Farm Equipment category deleted successfully!');
    }
}
