<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;



class ProfileController extends Controller
{
    // Display profile page
    public function index()
    {
        $user = Auth::user();

        // Fetch recent 5 bookings for this user (matched by email)
        $bookings = Booking::with(['service', 'stylist'])
            ->where('customer_email', $user->email)
            ->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->take(5)
            ->get();

        return view('profile.profilepage', compact('user','bookings')); 
    }

    // Show the edit profile form
    public function edit()
    {
        $user = Auth::user();
        return view('profile.editprofile', compact('user'));
    }

    // Handle form submission to update profile
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image|max:1024', // optional if handling profile photo
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profile-photos', $filename, 'public');
            $user->profile_photo_path = 'profile-photos/' . $filename;
        }

        $user->save();

        return redirect()->route('profile.page')->with('success', 'Profile updated successfully.');
    }

}
