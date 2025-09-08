<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stylist;

class StylistController extends Controller
{

public function index()
{
    // Fetch stylists with their services, ensuring only distinct services
    $stylists = Stylist::with(['services' => function($query) {
        $query->distinct();  // Ensure distinct services for each stylist
    }])->get();

    return view('stylists.index', compact('stylists'));
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

}   
