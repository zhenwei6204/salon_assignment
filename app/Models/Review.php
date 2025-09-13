<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'stylist_id',
        'user_id',
        'rating',
        'comment',
    ];

    // A review belongs to a stylist
    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }

    // A review belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

