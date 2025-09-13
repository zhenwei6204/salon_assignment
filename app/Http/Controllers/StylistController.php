<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stylist;
use App\Models\Service;

class StylistController extends Controller
{

    public function index(Request $request)
    {
        // Base query: only active stylists with eager-loaded relationships
        $query = Stylist::with('services', 'user')
                        ->where('is_active', 1);
    
        // --- SEARCH ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('specializations', 'like', "%{$search}%");
            });
        }
    
        // --- FILTER: experience years ---
        if ($request->filled('experience')) {
            $experience = $request->experience;
            if ($experience == '0-2') {
                $query->whereBetween('experience_years', [0, 2]);
            } elseif ($experience == '3-5') {
                $query->whereBetween('experience_years', [3, 5]);
            } elseif ($experience == '5+') {
                $query->where('experience_years', '>=', 5);
            }
        }
    
        // --- FILTER: rating ---
        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating);
        }
    
        // --- FILTER: service ---
        if ($request->filled('service')) {
            $service = $request->service;
            $query->whereHas('services', function ($q) use ($service) {
                $q->where('name', 'like', "%{$service}%");
            });
        }
    
        // --- DEFAULT SORTING ---
        $query->orderBy('rating', 'desc')->orderBy('experience_years', 'desc');
    
        // --- PAGINATION ---
        $stylists = $query->paginate(12)->withQueryString();
    
        // --- ALL SERVICES for dropdown ---
        $allServices = Service::pluck('name')->unique();
    
        return view('stylist.index', compact('stylists', 'allServices'));
    }


    public function assignStylistToService(Request $request)
{
    // Find the stylist and service based on the IDs passed in the request
    $stylist = Stylist::find($request->stylist_id);
    $service = Service::find($request->service_id);

    // If both exist, attach the stylist to the service (many-to-many relationship)
    if ($stylist && $service) {
        $stylist->services()->attach($service); // This can cause duplicates
        return redirect()->back()->with('success', 'Stylist assigned to service!');
    }

    return redirect()->back()->with('error', 'Stylist or Service not found!');
}

public function show($id)
{
    $stylist = Stylist::with('user', 'services')->findOrFail($id);
    return view('stylist.show', compact('stylist'));
}

}   
