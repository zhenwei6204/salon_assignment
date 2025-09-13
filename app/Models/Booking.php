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
        'user_id', // Make sure this is in fillable
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
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

    public function refunds()
{
    return $this->hasMany(Refund::class);
}

public function hasRefund()
{
    return $this->refunds()->exists();
}

public function canBeRefunded()
{
    // Check if booking status allows refunds
    if (!in_array($this->status, ['booked', 'completed','cancelled'])) {
        return false;
    }
    
    // Check if booking has a payment
    if (!$this->payment) {
        return false;
    }
    
    // Check if payment is completed
    if ($this->payment->status !== 'completed') {
        return false;
    }
    
    // Check if there are no pending/processing refunds
    if ($this->refunds()->whereIn('status', ['pending', 'approved', 'processing'])->exists()) {
        return false;
    }
    
    // Check if total refund amount is less than payment amount
    if ($this->totalRefundAmount() >= $this->payment->amount) {
        return false;
    }
    
    return true;
}

public function totalRefundAmount()
{
    return $this->refunds()
        ->whereIn('status', ['completed', 'approved', 'processing'])
        ->sum('refund_amount');
}
}