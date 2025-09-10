<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $mode;         // 'self' | 'other'
    public $bookerName;
    public $bookerEmail;

    public function __construct(Booking $booking, string $mode = 'self', ?string $bookerName = null, ?string $bookerEmail = null)
    {
        $this->booking     = $booking;
        $this->mode        = $mode;
        $this->bookerName  = $bookerName;
        $this->bookerEmail = $bookerEmail;
    }

    public function build()
    {
        return $this->subject('Your Booking Confirmation')
                    ->view('emails.booking_confirmation'); // you already have this view
    }
}