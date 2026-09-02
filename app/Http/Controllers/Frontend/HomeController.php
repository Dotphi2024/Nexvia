<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $featuredProducts = Product::where('status', 'active')->where('is_featured', true)->take(8)->get();
        $scooters = Product::whereHas('category', function($q) {
            $q->where('type', 'electric_mobility');
        })->where('status', 'active')->get();

        return view('frontend.home', compact('categories', 'featuredProducts', 'scooters'));
    }
}
