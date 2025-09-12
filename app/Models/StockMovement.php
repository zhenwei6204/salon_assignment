<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN  = 'in';
    public const TYPE_OUT = 'out';

    protected $fillable = [
        'item_id',
        'item_name',
        'booking_id',
        'type',
        'qty',
        'reason',
        'user_id',
        'source',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function item(): BelongsTo     { return $this->belongsTo(Item::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function booking(): BelongsTo  { return $this->belongsTo(Booking::class); }
}
