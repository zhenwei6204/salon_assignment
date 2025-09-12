<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\DB;

    class Item extends Model
    {
        use HasFactory;
        use SoftDeletes;

        protected $fillable = [
            'name',
            'sku',
            'unit',
            'stock',
            'reorder_level', 
        ];

        protected $casts = [
        'low_stock_notified_at' => 'datetime',   
    ];

        public function stockMovements(): HasMany
        {
            return $this->hasMany(StockMovement::class);
        }

      
        public function services(): BelongsToMany
        {
            return $this->belongsToMany(Service::class, 'service_item_consumptions')
                ->withPivot('qty_per_service')
                ->withTimestamps();
        }

        

    }
