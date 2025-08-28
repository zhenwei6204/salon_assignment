<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   public function index()
{
    $categories = Category::where('is_active', true)
                        ->withCount(['services' => function($query) {
                            $query->where('is_available', true);
                        }])
                        ->get();
                        
    // Also pass services for the main listing
    $services = Service::where('is_available', true)
                      ->with('category')
                      ->paginate(12);
                      
    return view('services.list', compact('categories', 'services'));
}
}