<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StylistService extends Model
{
    use HasFactory;

    protected $fillable = [
        'stylist_id',
        'service_id'
    ];

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}