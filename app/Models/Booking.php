<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }

    // Accessor for formatted date
    public function getFormattedDateAttribute()
    {
        return $this->booking_date->format('l, F j, Y');
    }

    // Accessor for formatted time
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->booking_time)->format('g:i A');
    }

    // Accessor for formatted end time
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('g:i A') : null;
    }
}