<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'title',
        'specializations',
        'experience_years',
        'is_active',
        'phone',
        'email',
        'bio',
        'image_url',
        'start_time',   // new: when stylist starts work
        'end_time',     // new: when stylist ends work
        'lunch_start',  // new: lunch start
        'lunch_end',    // new: lunch end
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'lunch_start' => 'datetime:H:i',
        'lunch_end' => 'datetime:H:i',
    ];

    public function user()
{
    return $this->belongsTo(\App\Models\User::class,'user_id');
}

    // Relationship: A stylist can perform many services (many-to-many)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'stylist_services');
    }

    // Relationship: A stylist has many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class); // assuming you have a Review model
    }

    // Dynamic rating: average of reviews
    public function getRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 2);
    }

    // Dynamic review count
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getAvailableSlots($serviceDuration, $date)
{
    $workingStart = $this->start_time ?? '10:00';
    $workingEnd = $this->end_time ?? '20:00';
    $lunchStart = $this->lunch_start ?? '13:00';
    $lunchEnd = $this->lunch_end ?? '14:00';

    $slots = [];
    $current = strtotime($workingStart);
    $end = strtotime($workingEnd);

    while ($current + $serviceDuration * 60 <= $end) {
        $slotStart = $current;
        $slotEnd = $current + $serviceDuration * 60;
        $slotStr = date('H:i', $slotStart);

        // skip lunch break
        if ($slotStart < strtotime($lunchEnd) && $slotEnd > strtotime($lunchStart)) {
            $current = strtotime($lunchEnd);
            continue;
        }

        // check if slot overlaps any existing booking
        $booked = $this->bookings()
                       ->where('booking_date', $date)
                       ->where(function ($query) use ($slotStart, $slotEnd) {
                           $query->whereRaw('booking_time < ? AND end_time > ?', [
                               date('H:i', $slotEnd),
                               date('H:i', $slotStart)
                           ]);
                       })
                       ->exists();

        if (!$booked) {
            $slots[] = $slotStr;
        }

        $current += $serviceDuration * 60;
    }

    return $slots;
}
}