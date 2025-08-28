<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistAvailability extends Model
{
    use HasFactory;

    protected $table = 'stylist_availability';

    protected $fillable = [
        'stylist_id',
        'date',
        'time_slot',
        'is_available'
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }
}