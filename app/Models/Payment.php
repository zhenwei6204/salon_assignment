<?php

namespace App\Models;
use App\Models\Booking; 
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

    public function refunds()
{
    return $this->hasMany(Refund::class);
}

public function latestRefund()
{
    return $this->hasOne(Refund::class)->latest();
}

public function hasRefund()
{
    return $this->refunds()->exists();
}

public function totalRefundAmount()
{
    return $this->refunds()->where('status', 'completed')->sum('refund_amount');
}

public function canBeRefunded()
{
    return $this->status === 'completed' || $this->status === 'cancelled' &&
           $this->totalRefundAmount() < $this->amount &&
           !$this->refunds()->whereIn('status', ['pending', 'approved', 'processing'])->exists();
}
}   