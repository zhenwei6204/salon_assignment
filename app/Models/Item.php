<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'stock',
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // services that consume this item
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_item_consumptions')
            ->withPivot('qty_per_service')
            ->withTimestamps();
    }
}
