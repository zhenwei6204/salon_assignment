<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Booking; 
class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

     protected function afterCreate(): void
    {
        // 1. Get the booking that was selected in the form.
        $booking = Booking::find($this->record->booking_id);

        // 2. If the booking is found...
        if ($booking) {
            // 3. ...update its 'payment_id' with the ID of the new payment.
            $booking->payment_id = $this->record->id;
            
            // 4. Save the change to the bookings table.
            $booking->save();
        }
    }
}
