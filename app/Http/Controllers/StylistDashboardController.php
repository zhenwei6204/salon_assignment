<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Stylist;
use Carbon\Carbon;

class StylistDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stylist = Auth::user()->stylistProfile()->with('services','user')->first();

        $date = $request->input('date', Carbon::today()->toDateString());
        $serviceDuration = $request->input('service_duration', 60);

        $availabilitySlots = $stylist->getAvailableSlots($serviceDuration, $date);

        $upcomingBookings = $stylist->bookings()
                                    ->where('booking_date', '>=', now()->toDateString())
                                    ->orderBy('booking_date')
                                    ->orderBy('booking_time')
                                    ->take(5)
                                    ->get();

        $recentReviews = $stylist->reviews()->with('user')->orderBy('created_at', 'desc')->take(5)->get();

        $stylist->review_count = $stylist->reviews()->count();
        $stylist->rating = $stylist->reviews()->avg('rating') ?? 0;

        return view('stylist.dashboard', compact(
            'stylist', 'upcomingBookings', 'recentReviews', 'availabilitySlots', 'date', 'serviceDuration'
        ));
    }

    public function updateProfile(Request $request)
    {
        $stylist = Auth::user()->stylistProfile;

        $request->validate([
            'name' => 'required|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'specializations' => 'nullable|string',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $stylist->update($request->only(
            'name', 'experience_years', 'specializations', 'bio', 'phone'
        ));

        // Update user email
        if ($request->filled('email')) {
            $stylist->user->update([
                'email' => $request->email,
            ]);
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $stylist->user->update(['profile_photo_path' => $path]);
        }

        // Auto-assign title based on experience
        if ($request->filled('experience_years')) {
            $years = $request->experience_years;

            if ($years >=10) {
                $stylist->title = 'Senior Stylist';
            } elseif ($years >= 5) {
                $stylist->title = 'Intermediate Stylist';
            } else {
                $stylist->title = 'Junior Stylist';
            }

            $stylist->save();
        }

        return redirect()->route('stylist.dashboard')->with('success', 'Profile updated successfully.');
    }

    public function updateSchedule(Request $request)
{
    $stylist = Auth::user()->stylistProfile;

    if (!$stylist) {
        return redirect()->back()->with('error', 'Stylist profile not found.');
    }

    // Define business hours
    $businessStart = '09:00';
    $businessEnd   = '18:00';

    // Validate format and logical order of inputs
    $request->validate([
        'start_time'   => "required|date_format:H:i|before:end_time",
        'end_time'     => "required|date_format:H:i|after:start_time",
        'lunch_start'  => 'nullable|date_format:H:i|after_or_equal:start_time|before_or_equal:end_time',
        'lunch_end'    => 'nullable|date_format:H:i|after:lunch_start|before_or_equal:end_time',
    ], [
        'start_time.before'          => 'Start time must be before end time.',
        'end_time.after'             => 'End time must be after start time.',
        'lunch_start.after_or_equal' => 'Lunch start must be after or equal to working start time.',
        'lunch_start.before_or_equal' => 'Lunch start must be before or equal to working end time.',
        'lunch_end.after'            => 'Lunch end must be after lunch start.',
        'lunch_end.before_or_equal'   => 'Lunch end must be before or equal to working end time.',
    ]);

    // Convert to Carbon for proper time comparison
    $newStart = Carbon::createFromFormat('H:i', $request->start_time);
    $newEnd   = Carbon::createFromFormat('H:i', $request->end_time);
    $businessStartTime = Carbon::createFromFormat('H:i', $businessStart);
    $businessEndTime   = Carbon::createFromFormat('H:i', $businessEnd);

    // Check against business hours - your working hours must be within business hours
    if ($newStart->lt($businessStartTime) || $newEnd->gt($businessEndTime)) {
        return redirect()->back()
            ->with('error', "Selected working hours must be within business hours ($businessStart - $businessEnd).")
            ->withInput();
    }

    // Prepare lunch times (if set)
    $lunchStart = $request->lunch_start ? Carbon::createFromFormat('H:i', $request->lunch_start) : null;
    $lunchEnd   = $request->lunch_end ? Carbon::createFromFormat('H:i', $request->lunch_end) : null;

    // Additional validation: lunch break must be fully within working hours
    if ($lunchStart && $lunchEnd) {
        if ($lunchStart->lt($newStart) || $lunchEnd->gt($newEnd)) {
            return redirect()->back()
                ->with('error', 'Lunch break must be within your working hours.')
                ->withInput();
        }
    }

    // Get future bookings
    $futureBookings = $stylist->bookings()
        ->where('status', '!=', 'cancelled')
        ->where('booking_date', '>=', now()->toDateString())
        ->get();

    $conflicts = [];

    foreach ($futureBookings as $booking) {
        try {
            // Handle booking time parsing more safely
            if ($booking->booking_time instanceof \Carbon\Carbon) {
                $bookingStart = Carbon::createFromFormat('H:i', $booking->booking_time->format('H:i'));
                $bookingEnd = Carbon::createFromFormat('H:i', $booking->end_time->format('H:i'));
            } else {
                // If it's stored as time string
                $bookingStart = Carbon::createFromFormat('H:i:s', $booking->booking_time)->format('H:i');
                $bookingStart = Carbon::createFromFormat('H:i', $bookingStart);
                $bookingEnd = Carbon::createFromFormat('H:i:s', $booking->end_time)->format('H:i');
                $bookingEnd = Carbon::createFromFormat('H:i', $bookingEnd);
            }

            // CONFLICT 1: Booking falls OUTSIDE the new working hours
            if ($bookingStart->lt($newStart) || $bookingEnd->gt($newEnd)) {
                $conflicts[] = [
                    'type' => 'working_hours',
                    'booking' => $booking,
                    'message' => "Booking on {$booking->booking_date} ({$bookingStart->format('H:i')} - {$bookingEnd->format('H:i')}) with {$booking->customer_name} falls outside new working hours"
                ];
            }

            // CONFLICT 2: Booking overlaps with lunch break
            if ($lunchStart && $lunchEnd) {
                if ($bookingStart->lt($lunchEnd) && $bookingEnd->gt($lunchStart)) {
                    $conflicts[] = [
                        'type' => 'lunch_break',
                        'booking' => $booking,
                        'message' => "Booking on {$booking->booking_date} ({$bookingStart->format('H:i')} - {$bookingEnd->format('H:i')}) with {$booking->customer_name} conflicts with lunch break ({$request->lunch_start} - {$request->lunch_end})"
                    ];
                }
            }
        } catch (\Exception $e) {
            // Skip bookings with invalid time formats
            \Log::warning('Invalid booking time format', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            continue;
        }
    }

    // If there are conflicts, show them to the user
    if (count($conflicts) > 0) {
        $conflictMessages = array_map(function($conflict) {
            return $conflict['message'];
        }, $conflicts);

        return redirect()->back()
            ->with('error', 'Cannot update schedule due to the following conflicts: ' . implode('; ', $conflictMessages))
            ->withInput();
    }

    // No conflicts, safe to update
    $stylist->update($request->only('start_time', 'end_time', 'lunch_start', 'lunch_end'));

    return redirect()->route('stylist.dashboard')
        ->with('success', 'Schedule updated successfully.');
}


}
