<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
{
    $query = Service::query()->where('is_available', true)->with('category');
    
    // Category filter
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }
    
    // Search filter
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }
    
    // Price range filter
    if ($request->filled('price_range')) {
        $range = $request->price_range;
        if ($range === '0-50') {
            $query->where('price', '<=', 50);
        } elseif ($range === '51-100') {
            $query->whereBetween('price', [51, 100]);
        } elseif ($range === '101-200') {
            $query->whereBetween('price', [101, 200]);
        } elseif ($range === '201+') {
            $query->where('price', '>', 200);
        }
    }
    
    // Duration filter
    if ($request->filled('duration')) {
        $duration = $request->duration;
        if ($duration === '0-30') {
            $query->where('duration', '<=', 30);
        } elseif ($duration === '31-60') {
            $query->whereBetween('duration', [31, 60]);
        } elseif ($duration === '61-120') {
            $query->whereBetween('duration', [61, 120]);
        } elseif ($duration === '121+') {
            $query->where('duration', '>', 120);
        }
    }
    
    $services = $query->paginate(12);
    
    // Get categories for the tabs
    $categories = Category::where('is_active', true)
                         ->withCount(['services' => function($query) {
                             $query->where('is_available', true);
                         }])
                         ->get();
    
    return view('services.list', compact('services', 'categories'));
}
    
        public function show($id)
    {
        // Fetch the service by its ID, along with the category and related stylists
        $service = Service::with('category', 'stylists')->findOrFail($id);

        // Get related services from the same category
        $relatedServices = Service::where('category_id', $service->category_id)
                                ->where('id', '!=', $service->id)
                                ->take(3)
                                ->get();

        // Return the service details view with the data
        return view('services.show', compact('service', 'relatedServices'));
    }
}