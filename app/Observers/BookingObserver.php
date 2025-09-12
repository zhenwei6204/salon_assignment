<?php

namespace App\Observers;

use App\Models\Booking;

use App\Services\InventoryService;

class BookingObserver
{

    
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        $old = $booking->getOriginal("status");
        $new = $booking->status;

        if ($old === $new) {
            return;
        }

        $inventory = app(InventoryService::class);

        if ($new === "completed") {
            $inventory->deductForBooking($booking);
        }

        if ($new === "cancelled") {
            $inventory->revertForBooking($booking);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}
