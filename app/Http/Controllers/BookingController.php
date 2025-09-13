<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Stylist;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\BookingFacade;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    /**
     * Step 1: Select a stylist for the service
     */
    public function selectStylist(Service $service)
    {
        $stylists = $service->stylists()->where('is_active', true)->get();
        return view('booking.category.stylists', compact('service', 'stylists'));
    }

    /**
     * Step 2: Choose a date and time
     */
    public function chooseTime(Request $request, Service $service, Stylist $stylist)
    {
        $selectedDate   = $request->query('date', now()->format('Y-m-d'));
        $selectedTime   = $request->query('time');
        $availableSlots = $this->buildAvailableSlots($service, $stylist, $selectedDate);

        return view('booking.category.times', compact(
            'service', 'stylist', 'selectedDate', 'selectedTime', 'availableSlots'
        ));
    }

    // Alias if your routes still call selectTime
    public function selectTime(Request $request, Service $service, Stylist $stylist)
    {
        return $this->chooseTime($request, $service, $stylist);
    }

    /**
     * Step 3: Show confirmation page before payment
     */
    public function confirm(Request $request, Service $service, Stylist $stylist)
    {
        $selectedDate = $request->query('date');
        $selectedTime = $request->query('time');
        $endTimePreview = null;

        // Validate required parameters
        if (!$selectedDate || !$selectedTime) {
            return redirect()->route('booking.select.time', [$service, $stylist])
                ->with('error', 'Please select a date and time first.');
        }

        // Calculate end time preview
        if ($selectedDate && $selectedTime && ($service->duration ?? 0) > 0) {
            $start = Carbon::parse($selectedDate . ' ' . $selectedTime, config('app.timezone'));
            $endTimePreview = $start->copy()->addMinutes((int) $service->duration)->format('H:i');
        }

        return view('booking.category.confirmation', compact(
            'service', 'stylist', 'selectedDate', 'selectedTime', 'endTimePreview'
        ));
    }

   /**
     * Step 4: Create booking (but don't process payment yet)
     * Then redirect to payment page - SELF BOOKING ONLY
     */
    public function store(Request $request)
    {
        // Ensure user is authenticated for self-booking
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to make a booking.');
        }

        $validator = Validator::make($request->all(), [
            'service_id'     => 'required|exists:services,id',
            'stylist_id'     => 'required|exists:stylists,id',
            'booking_date'   => 'required|date',
            'booking_time'   => 'required',
            'customer_phone' => 'required|string|max:20', // Made required for self-booking
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            // Load the service and stylist with their relationships
            $service = Service::findOrFail($request->service_id);
            $stylist = Stylist::findOrFail($request->stylist_id);
            $user = $request->user();

            // Create the booking using the facade - ALWAYS self booking
            /** @var BookingFacade $facade */
            $facade = app(BookingFacade::class);
            $booking = $facade->createBooking(
                $service,
                $stylist,
                $request->booking_date,
                $request->booking_time,
                'self', // Always self
                $user,
                $request->customer_phone,
                null, // No other name
                null, // No other email
                null  // No other phone
            );

            // Create the payment record for the new booking
            $price = $service->price;
            $payment = Payment::create([
                'booking_id'    => $booking->id,
                'amount'        => $price,
                'status'        => 'pending',
                'payment_ref'   => 'PAY-' . now()->format('Ymd') . '-' . strtoupper(str()->random(8)),
            ]);

            // Store stylist info for payment page
            $stylistArray = [
                'id' => $stylist->id,
                'name' => $stylist->name,
                'email' => $stylist->email ?? '',
                'phone' => $stylist->phone ?? '',
                'specializations' => $stylist->specializations ?? '',
            ];

            // Store COMPLETE booking and payment details in session for payment page
            session()->put('booking_details', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'customer_name' => $booking->customer_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'service_name' => $service->name,
                'service_id' => $service->id,
                'stylist_name' => $stylist->name,
                'stylist_id' => $stylist->id,
                'booking_date' => $booking->booking_date,
                'booking_time' => $booking->booking_time,
                'end_time' => $booking->end_time,
                'booking_reference' => $booking->booking_reference,
                'amount' => $price,
                'stylist' => $stylistArray
            ]);

            DB::commit();

            Log::info('Self-booking created successfully, redirecting to payment', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'service_id' => $service->id,
                'user_id' => $user->id
            ]);

            // Redirect to payment page
            return redirect()
                ->route('booking.payment.makePayment', $request->service_id)
                ->with('success', 'Booking created! Please complete payment to confirm.');

        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Unexpected error during self-booking creation', [
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
                'user_id'      => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Success page (called after successful payment)
     */
    public function success($id)
    {
        $booking = Booking::with(['service', 'stylist'])->findOrFail($id);
        
        // Optional: Check if payment is completed
        $payment = Payment::where('booking_id', $booking->id)->first();
        
        return view('booking.category.success', compact('booking', 'payment'));
    }

    /**
     * My Bookings - Updated to use user_id foreign key
     * - Shows bookings that belong to the user (by user_id OR email as fallback)
     * - Prioritizes user_id relationship for better data integrity
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();
        
        // Query bookings using user_id (preferred) OR email (fallback for old bookings)
        $query = \App\Models\Booking::with(['service', 'stylist', 'user'])
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhere('customer_email', $user->email);
            });

        // Search (reference, service name, stylist name)
        if ($q = trim($request->get('q', ''))) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('booking_reference', 'like', "%{$q}%")
                    ->orWhereHas('service', function ($s) use ($q) {
                        $s->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('stylist', function ($st) use ($q) {
                        $st->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if (in_array($status, ['booked', 'cancelled', 'completed'], true)) {
                $query->where('status', $status);
            }
        }

        // Date range filter
        if ($from = $request->get('from')) {
            $query->whereDate('booking_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('booking_date', '<=', $to);
        }

        // Order newest first
        $query->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc');

        $bookings = $query->paginate(10)->withQueryString();

        return view('profile.my_bookings', [
            'bookings' => $bookings,
            'filters'  => [
                'q'      => $request->get('q', ''),
                'status' => $request->get('status', ''),
                'from'   => $request->get('from', ''),
                'to'     => $request->get('to', ''),
            ],
        ]);
    }

    /**
 * Cancel a booking - Updated to use user_id foreign key
 */
    public function cancel(Request $request, Booking $booking)
    {
        /** @var \App\Services\BookingFacade $facade */
        $facade = app(\App\Services\BookingFacade::class);

        try {
            $facade->cancelBooking($booking, $request->user());
            return redirect()->route('bookings.index')
                ->with('success', 'Booking cancelled successfully. A confirmation email has been sent.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
 * Helper: build available slots for a day/stylist/service duration
 */
private function buildAvailableSlots(Service $service, Stylist $stylist, string $date): array
{
    $duration = (int)($service->duration ?? 0);
    if ($duration <= 0) return [];

    $appTimezone = config('app.timezone');
    $now = \Carbon\Carbon::now($appTimezone);

    // Use stylist's working hours, not business hours
    // If stylist hasn't set their hours, default to business hours
    $stylistStart = $stylist->start_time ?? '09:00:00';
    $stylistEnd = $stylist->end_time ?? '18:00:00';

    // Extract just the time part if it's a datetime string
    $stylistStart = $this->extractTime($stylistStart);
    $stylistEnd = $this->extractTime($stylistEnd);

    $businessStart = \Carbon\Carbon::parse($date . ' '. $stylistStart, $appTimezone);
    $businessEnd   = \Carbon\Carbon::parse($date . ' '. $stylistEnd, $appTimezone);

    // Fetch existing bookings for the selected date
    $bookings = Booking::where('stylist_id', $stylist->id)
        ->whereDate('booking_date', $date)
        ->where('status', '!=', 'cancelled')
        ->get(['booking_time', 'end_time']);

    $busy = $bookings->map(function($b) use ($date, $appTimezone) {
        $startTime = $this->extractTime($b->booking_time);
        $endTime = $this->extractTime($b->end_time);
        
        return [
            \Carbon\Carbon::parse($date . ' ' . $startTime, $appTimezone),
            \Carbon\Carbon::parse($date . ' ' . $endTime, $appTimezone),
        ];
    });

    // Add lunch break to busy intervals if lunch times are set
    if ($stylist->lunch_start && $stylist->lunch_end) {
        $lunchStartTime = $this->extractTime($stylist->lunch_start);
        $lunchEndTime = $this->extractTime($stylist->lunch_end);
        
        $lunchStart = \Carbon\Carbon::parse($date . ' ' . $lunchStartTime, $appTimezone);
        $lunchEnd = \Carbon\Carbon::parse($date . ' ' . $lunchEndTime, $appTimezone);
    
        $busy->push([$lunchStart, $lunchEnd]);
    }

    $slots = [];
    for ($cursor = $businessStart->copy(); $cursor->lt($businessEnd); $cursor->addMinutes($duration)) {
        $slotStart = $cursor->copy();
        $slotEnd   = $cursor->copy()->addMinutes($duration);
        if ($slotEnd->gt($businessEnd)) break;

        // If the selected date is today, check if the slot time has already passed.
        if ($businessStart->isToday() && $slotStart->isBefore($now->addMinutes(5))) {
            continue;
        }

        $overlaps = $busy->first(fn($int) => $slotStart->lt($int[1]) && $slotEnd->gt($int[0]));
        if (!$overlaps) {
            $slots[] = $slotStart->format('H:i');
        }
    }

    return $slots;
}

// Helper method to extract time from datetime string
private function extractTime($datetime)
{
    if ($datetime instanceof \Carbon\Carbon) {
        return $datetime->format('H:i:s');
    }
    
    if (is_string($datetime)) {
        // If it's already just a time string like "12:00:00"
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $datetime)) {
            return $datetime;
        }
        
        // If it's a datetime string, extract the time part
        try {
            $carbon = \Carbon\Carbon::parse($datetime);
            return $carbon->format('H:i:s');
        } catch (\Exception $e) {
            // Fallback: try to extract time manually
            if (preg_match('/(\d{1,2}:\d{2}:\d{2})/', $datetime, $matches)) {
                return $matches[1];
            }
            if (preg_match('/(\d{1,2}:\d{2})/', $datetime, $matches)) {
                return $matches[1] . ':00';
            }
        }
    }
    
    return '00:00:00'; // Default fallback
}
}