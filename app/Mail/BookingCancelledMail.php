<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $appName = config('app.name');
        $brand = ($appName && $appName !== 'Laravel') ? $appName : 'Salon Good';

        return $this->subject('Booking Cancelled - ' . $brand)
                    ->view('emails.booking_cancelled'); // <- new blade
    }
}
