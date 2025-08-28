<?php

namespace App\Http\Controllers;
use App\Models\Service; 
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home() { 
        
           {
        // Fetch only the top 3 services
        $services = Service::take(3)->get(); // Fetch top 3 services

        // Pass the services to the home view
           return view('home', compact('services'));}
    }

}
