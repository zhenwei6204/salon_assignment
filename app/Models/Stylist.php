<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'specializations',
        'experience_years',
        'rating',
        'review_count',
        'is_active',
        'phone',
        'email',
        'bio',
        'image_url'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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

    // Relationship: A stylist has availability slots
    public function availability()
    {
        return $this->hasMany(StylistAvailability::class);
    }
}