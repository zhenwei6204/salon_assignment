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

class BookingFacade
{
    /**
     * Create a booking:
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
        string $bookingFor,      // 'self'|'other'
        ?\App\Models\User $actingUser,
        ?string $customerPhone,  // used when booking for self or fallback
        ?string $otherName,      // used when booking for other
        ?string $otherEmail,     // used when booking for other
        ?string $otherPhone      // used when booking for other
    ): Booking {

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

        // Determine customer details
        $customerName  = $bookingFor === 'other'
            ? ($otherName  ?: ($actingUser?->name ?? ''))
            : ($actingUser?->name ?? '');

        $customerEmail = $bookingFor === 'other'
            ? ($otherEmail ?: ($actingUser?->email ?? ''))
            : ($actingUser?->email ?? '');

        $finalPhone    = $bookingFor === 'other'
            ? ($otherPhone ?: $customerPhone)
            : $customerPhone;

        // Snapshot the price at booking time
        $priceSnapshot = (float) ($service->price ?? 0);

        // Build a reference
        $reference = $this->buildReference();

        // Persist in a transaction
        $booking = DB::transaction(function () use (
            $service, $stylist, $customerName, $customerEmail, $finalPhone,
            $bookingDate, $bookingTime, $end, $priceSnapshot, $reference
        ) {
            $b = new Booking();
            $b->service_id        = $service->id;
            $b->stylist_id        = $stylist->id;
            $b->customer_name     = $customerName;
            $b->customer_email    = $customerEmail;
            $b->customer_phone    = $finalPhone;
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
            if (!empty($booking->customer_email)) {
                Mail::to($booking->customer_email)->send(new BookingConfirmationMail($booking));
            }
        } catch (\Throwable $mailEx) {
            Log::warning('Booking created but email failed', [
                'booking_id' => $booking->id,
                'error'      => $mailEx->getMessage(),
            ]);
        }

        // Log success
        Log::info('Booking created', [
            'booking_id'   => $booking->id,
            'service_id'   => $service->id,
            'stylist_id'   => $stylist->id,
            'date'         => $booking->booking_date,
            'time'         => $booking->booking_time,
            'total_price'  => $booking->total_price,
        ]);

        return $booking;
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
            // FIX: Safely extract only the time from the stored string.
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