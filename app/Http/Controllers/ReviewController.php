<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Stylist;

class ReviewController extends Controller
{
    public function store(Request $request, Stylist $stylist)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'stylist_id' => $stylist->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update stylist's review stats
        $stylist->review_count = $stylist->reviews()->count();
        $stylist->rating = $stylist->reviews()->avg('rating');
        $stylist->save();

        return redirect()->route('stylists.show', $stylist->id)
                         ->with('success', 'Your review has been submitted!');
    }
}
