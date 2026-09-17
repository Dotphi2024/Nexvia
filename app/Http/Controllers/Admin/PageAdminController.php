<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageAdminController extends Controller
{
    /**
     * Display a listing of all pages.
     */
    public function index(Request $request)
    {
        $query = Page::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $pages = $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->paginate(15)->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show form for creating a new page.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created page in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug',
            'excerpt'          => 'nullable|string|max:1000',
            'content'          => 'nullable|string',
            'points'           => 'nullable|array',
            'points.*.title'   => 'nullable|string|max:255',
            'points.*.description' => 'nullable|string|max:2000',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords'    => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Ensure unique slug
        $baseSlug = $slug;
        $count = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        // Clean up points array
        $points = [];
        if ($request->has('points') && is_array($request->points)) {
            foreach ($request->points as $point) {
                $pTitle = trim($point['title'] ?? '');
                $pDesc = trim($point['description'] ?? '');
                if (!empty($pTitle) || !empty($pDesc)) {
                    $points[] = [
                        'title'       => $pTitle,
                        'description' => $pDesc,
                    ];
                }
            }
        }

        $page = Page::create([
            'title'            => $request->title,
            'slug'             => $slug,
            'excerpt'          => $request->excerpt,
            'content'          => $request->content,
            'points'           => $points,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords'    => $request->meta_keywords,
            'is_active'        => $request->has('is_active'),
            'sort_order'       => $request->sort_order ?? (Page::max('sort_order') + 1),
        ]);

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' created successfully!");
    }

    /**
     * Show form for editing the specified page.
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page in database.
     */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'excerpt'          => 'nullable|string|max:1000',
            'content'          => 'nullable|string',
            'points'           => 'nullable|array',
            'points.*.title'   => 'nullable|string|max:255',
            'points.*.description' => 'nullable|string|max:2000',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords'    => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Ensure uniqueness if slug changed
        if ($slug !== $page->slug) {
            $baseSlug = $slug;
            $count = 1;
            while (Page::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }
            $page->slug = $slug;
        }

        // Clean up points array
        $points = [];
        if ($request->has('points') && is_array($request->points)) {
            foreach ($request->points as $point) {
                $pTitle = trim($point['title'] ?? '');
                $pDesc = trim($point['description'] ?? '');
                if (!empty($pTitle) || !empty($pDesc)) {
                    $points[] = [
                        'title'       => $pTitle,
                        'description' => $pDesc,
                    ];
                }
            }
        }

        $page->title            = $request->title;
        $page->excerpt          = $request->excerpt;
        $page->content          = $request->content;
        $page->points           = $points;
        $page->meta_title       = $request->meta_title;
        $page->meta_description = $request->meta_description;
        $page->meta_keywords    = $request->meta_keywords;
        $page->is_active        = $request->has('is_active');
        if ($request->filled('sort_order')) {
            $page->sort_order   = (int) $request->sort_order;
        }
        $page->save();

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' updated successfully!");
    }

    /**
     * Toggle page active status.
     */
    public function toggleStatus($id)
    {
        $page = Page::findOrFail($id);
        $page->is_active = !$page->is_active;
        $page->save();

        $statusText = $page->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Page '{$page->title}' has been {$statusText} successfully.");
    }

    /**
     * Remove the specified page from database.
     */
    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $title = $page->title;
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', "Page '{$title}' deleted successfully!");
    }
}
