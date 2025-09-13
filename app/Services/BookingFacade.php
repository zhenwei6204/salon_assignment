<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingCancelledMail;

class BookingFacade
{
    /**
     * Create a booking for the authenticated user only:
     * - validates availability
     * - computes end time and total price (snapshot from Service)
     * - writes in a DB transaction
     * - emails confirmation
     */
    public function createBooking(
        Service $service,
        Stylist $stylist,
        string $bookingDate,      // 'Y-m-d'
        string $bookingTime,      // 'H:i'
        string $bookingFor,      // Always 'self' now
        ?\App\Models\User $actingUser,
        ?string $customerPhone,  // Required phone number
    ): Booking {

        // Validate user is authenticated
        if (!$actingUser) {
            throw new \RuntimeException('User must be authenticated to make a booking.');
        }

        // Validate booking is for self only
        if ($bookingFor !== 'self') {
            throw new \RuntimeException('Only self-bookings are allowed.');
        }

        // Validate phone is provided
        if (empty($customerPhone)) {
            throw new \RuntimeException('Phone number is required.');
        }

        // Prepare times
        $duration = (int) ($service->duration ?? 0);
        if ($duration <= 0) {
            throw new \RuntimeException('Service duration is not set.');
        }

        $start = Carbon::parse($bookingDate . ' ' . $bookingTime, config('app.timezone'));
        $end   = $start->copy()->addMinutes($duration);

        // Business hours guard (optional, adjust to your needs)
        $businessStart = Carbon::parse($bookingDate . ' 09:00:00', config('app.timezone'));
        $businessEnd   = Carbon::parse($bookingDate . ' 18:00:00', config('app.timezone'));

        if ($start->lt($businessStart) || $end->gt($businessEnd)) {
            throw new \RuntimeException('Selected time is outside business hours.');
        }

        // Check slot availability for this stylist
        if (!$this->isSlotAvailable($stylist, $bookingDate, $start, $end)) {
            throw new \RuntimeException('The selected time slot is not available.');
        }

        // Use authenticated user's details (self-booking only)
        $customerName  = $actingUser->name;
        $customerEmail = $actingUser->email;

        // Snapshot the price at booking time
        $priceSnapshot = (float) ($service->price ?? 0);

        // Build a reference
        $reference = $this->buildReference();

        // Persist in a transaction
        $booking = DB::transaction(function () use (
            $service, $stylist, $customerName, $customerEmail, $customerPhone,
            $bookingDate, $bookingTime, $end, $priceSnapshot, $reference, $actingUser
        ) {
            $b = new Booking();
            $b->service_id        = $service->id;
            $b->stylist_id        = $stylist->id;
            $b->user_id           = $actingUser->id; // Always set for authenticated user
            $b->customer_name     = $customerName;
            $b->customer_email    = $customerEmail;
            $b->customer_phone    = $customerPhone;
            $b->booking_date      = $bookingDate;
            $b->booking_time      = $bookingTime;
            $b->end_time          = $end->format('H:i');
            $b->status            = 'booked';
            $b->booking_reference = $reference;
            $b->total_price       = $priceSnapshot;
            $b->save();

            return $b;
        });

        // Send confirmation email (best-effort: do not block success if mail fails)
        try {
            // Send confirmation to the user who made the booking
            if (!empty($booking->customer_email)) {
                Mail::to($booking->customer_email)->send(new BookingConfirmationMail($booking, 'self'));
            }
        } catch (\Throwable $mailEx) {
            Log::warning('Self-booking created but email failed', [
                'booking_id' => $booking->id,
                'user_id'    => $actingUser->id,
                'error'      => $mailEx->getMessage(),
            ]);
        }

        // Log success
        Log::info('Self-booking created successfully', [
            'booking_id'   => $booking->id,
            'service_id'   => $service->id,
            'stylist_id'   => $stylist->id,
            'user_id'      => $booking->user_id,
            'date'         => $booking->booking_date,
            'time'         => $booking->booking_time,
            'total_price'  => $booking->total_price,
        ]);

        return $booking;
    }

    public function cancelBooking(\App\Models\Booking $booking, \App\Models\User $actingUser): \App\Models\Booking
    {
        // Ownership/auth checks
        if ($booking->user_id !== $actingUser->id && $booking->customer_email !== $actingUser->email) {
            throw new \RuntimeException('You are not allowed to cancel this booking.');
        }
        if ($booking->status === 'completed') {
            throw new \RuntimeException('Completed bookings cannot be cancelled.');
        }
        if ($booking->status === 'cancelled') {
            throw new \RuntimeException('This booking is already cancelled.');
        }

        $tz = config('app.timezone');

        // Get a pure Y-m-d and pure H:i:s from stored values (works whether columns are strings or Carbon)
        $dateYmd   = $booking->booking_date instanceof Carbon
            ? $booking->booking_date->format('Y-m-d')
            : date('Y-m-d', strtotime((string)$booking->booking_date));

        $startTime = $this->onlyTime($booking->booking_time); // e.g. "11:00:00"
        $endTime   = $this->onlyTime($booking->end_time);     // e.g. "12:00:00"

        // Build correct Carbon instances (one date + one time)
        $start = Carbon::parse($dateYmd.' '.$startTime, $tz);
        $end   = Carbon::parse($dateYmd.' '.$endTime,   $tz);

        // Business rule: cannot cancel within 2 hours of appointment
        if ($start->lte(now($tz)->addHours(2))) {
            throw new \RuntimeException('Too close to appointment, cannot cancel.');
        }

        // Update status
        $booking->status = 'cancelled';
        $booking->save();

        // Send cancellation email (best-effort)
        try {
            $recipient = $booking->customer_email ?: optional($booking->user)->email;
            Log::info('About to send BookingCancelledMail', ['booking_id' => $booking->id, 'to' => $recipient]);

            if ($recipient) {
                Mail::to($recipient)->send(new BookingCancelledMail($booking));
                Log::info('BookingCancelledMail sent', ['booking_id' => $booking->id]);
            } else {
                Log::warning('No recipient email found for cancelled booking', ['booking_id' => $booking->id]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed sending BookingCancelledMail', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return $booking;
    }

    /**
     * Return time-of-day as "H:i:s" regardless of whether the value is string or Carbon.
     */
    private function onlyTime($value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }
        // If it's already "H:i" or "H:i:s", keep it; else parse safely
        $ts = strtotime((string)$value);
        return $ts ? date('H:i:s', $ts) : '00:00:00';
    }



    /**
     * Check the stylist's availability for the given period.
     * A simple overlap check against non-cancelled bookings.
     */
    protected function isSlotAvailable(Stylist $stylist, string $dateYmd, Carbon $slotStart, Carbon $slotEnd): bool
    {
        // Fetch existing bookings for the day (excluding cancelled)
        $existing = Booking::where('stylist_id', $stylist->id)
            ->whereDate('booking_date', $dateYmd)
            ->where('status', '!=', 'cancelled')
            ->get(['booking_time', 'end_time']);

        foreach ($existing as $b) {
            // Safely extract only the time from the stored string.
            $existingBookingTime = date('H:i', strtotime($b->booking_time));
            $existingEndTime = date('H:i', strtotime($b->end_time));
            
            $bStart = Carbon::parse($dateYmd . ' ' . $existingBookingTime, config('app.timezone'));
            $bEnd   = Carbon::parse($dateYmd . ' ' . $existingEndTime, config('app.timezone'));
            
            // intervals overlap if start < existingEnd && end > existingStart
            if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a human-friendly booking reference.
     */
    protected function buildReference(): string
    {
        return 'BKG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
    }
}