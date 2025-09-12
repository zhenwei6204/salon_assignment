<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'benefits',
        'price',
        'duration',
        'is_available',
        'stylist_qualifications',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Relationship: A service belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship: A service can be performed by many stylists (many-to-many)
    public function stylists()
    {
        return $this->belongsToMany(Stylist::class, 'stylist_services');
    }

    // Relationship: A service has many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Get category name attribute
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'No Category';
    }

    // Track how many items consumed per service 
    public function consumedItems(): BelongsToMany    
    {
        return $this->belongsToMany(Item::class, 'service_item_consumptions')
            ->withPivot('qty_per_service')
            ->withTimestamps();
    }
}