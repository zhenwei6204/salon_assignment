<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

     protected $fillable = [
        'service_id',
        'stylist_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'booking_date',
        'booking_time',
        'end_time',
        'total_price',
        'status',
        'booking_reference',
        'special_requests',
        'notes',
        'payment_id',
    ];


    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime',
        'end_time' => 'datetime',
        'total_price' => 'decimal:2'
    ];

      public function service()
    {
        return $this->belongsTo(Service::class);
    }

   public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }
   public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }


    /**
     * Check if booking has payment
     */
    public function hasPayment()
    {
        return $this->payment !== null;
    }

    /**
     * Get payment status for this booking
     */
    public function getPaymentStatusAttribute()
    {
        return $this->payment?->status ?? 'no_payment';
    }

    /**
     * Get payment method for this booking
     */
    public function getPaymentMethodAttribute()
    {
        return $this->payment?->payment_method ?? null;
    }

    /**
     * Check if booking is fully paid
     */
    public function isPaid()
    {
        return $this->payment && $this->payment->isCompleted();
    }

    /**
     * Check if payment is pending
     */
    public function isPaymentPending()
    {
        return $this->payment && $this->payment->isPending();
    }

    /**
     * Get formatted booking status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}