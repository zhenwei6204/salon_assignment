<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_method', // enum('cash','paypal','credit_card','bank_transfer')
        'amount',         // double
        'status'          // enum('pending','completed','failed')
    ];

    protected $casts = [
        'amount' => 'double'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payment status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning', 
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get formatted payment method name
     */
    public function getFormattedPaymentMethodAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Cash Payment at Salon',
            'credit_card' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if this is a cash payment
     */
    public function isCashPayment()
    {
        return $this->payment_method === 'cash';
    }
}