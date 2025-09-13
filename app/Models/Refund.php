<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'booking_id',
        'refund_reference',
        'refund_amount',
        'original_amount',
        'refund_type',
        'status',
        'reason',
        'admin_notes',
        'refund_method',
        'requested_at',
        'approved_at',
        'processed_at',
        'completed_at',
        'requested_by',
        'approved_by',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'processing' => 'primary',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            default => ucfirst($this->status)
        };
    }

    public function getRefundPercentageAttribute()
    {
        if ($this->original_amount > 0) {
            return round(($this->refund_amount / $this->original_amount) * 100, 2);
        }
        return 0;
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

     public function canBeApproved()
    {
        return $this->status === 'Pending';
    }


    // Auto-generate refund reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_reference)) {
                do {
                    $reference = 'REF-' . date('Ymd') . '-' . strtoupper(Str::random(5));
                } while (self::where('refund_reference', $reference)->exists());
                
                $refund->refund_reference = $reference;
            }
            if (empty($refund->requested_at)) {
                $refund->requested_at = now();
            }
        });
    }

    // Scopes
    public function scopeForUser($query, $userEmail)
    {
        return $query->whereHas('booking', function($q) use ($userEmail) {
            $q->where('customer_email', $userEmail);
        });
    }
}