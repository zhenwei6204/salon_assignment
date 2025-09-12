<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN  = 'in';
    public const TYPE_OUT = 'out';

    protected $fillable = ['item_id', 'booking_id', 'type', 'qty', 'reason', 'user_id'];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    protected static function booted(): void
    {
        // CREATE  ------------------------------------------------------------
        static::created(function (StockMovement $m) {
            DB::transaction(function () use ($m) {
                $delta = $m->type === self::TYPE_IN ? +$m->qty : -$m->qty;

                // lock the item row while adjusting
                $item = Item::whereKey($m->item_id)->lockForUpdate()->firstOrFail();

                if ($item->stock + $delta < 0) {
                    throw new InvalidArgumentException('Insufficient stock.');
                }

                $item->increment('stock', $delta);
            });
        });

        // UPDATE  -------------------------------------------------------------
        static::updated(function (StockMovement $m) {
            DB::transaction(function () use ($m) {

                // If the movement was moved to another item, revert old item and apply new
                if ($m->wasChanged('item_id')) {
                    $oldItemId = $m->getOriginal('item_id');
                    $oldType   = $m->getOriginal('type');
                    $oldQty    = (int) $m->getOriginal('qty');

                    $oldDelta  = $oldType === self::TYPE_IN ? +$oldQty : -$oldQty;
                    $newDelta  = $m->type   === self::TYPE_IN ? +$m->qty  : -$m->qty;

                    $oldItem = Item::whereKey($oldItemId)->lockForUpdate()->first();
                    $newItem = Item::whereKey($m->item_id)->lockForUpdate()->firstOrFail();

                    if ($oldItem) {
                        // revert old
                        if ($oldItem->stock - $oldDelta < 0) {
                            throw new InvalidArgumentException('Cannot revert stock on old item below zero.');
                        }
                        $oldItem->increment('stock', -$oldDelta);
                    }

                    // apply new
                    if ($newItem->stock + $newDelta < 0) {
                        throw new InvalidArgumentException('Insufficient stock on new item.');
                    }
                    $newItem->increment('stock', $newDelta);

                    return; // important: avoid running the qty/type diff below
                }

                // Same item: adjust by difference in qty/type
                if ($m->wasChanged(['type', 'qty'])) {
                    $oldDelta = ($m->getOriginal('type') === self::TYPE_IN ? +$m->getOriginal('qty') : -$m->getOriginal('qty'));
                    $newDelta = ($m->type               === self::TYPE_IN ? +$m->qty               : -$m->qty);

                    $diff = $newDelta - $oldDelta;

                    $item = Item::whereKey($m->item_id)->lockForUpdate()->firstOrFail();

                    if ($item->stock + $diff < 0) {
                        throw new InvalidArgumentException('Insufficient stock.');
                    }

                    $item->increment('stock', $diff);
                }
            });
        });

        // DELETE  -------------------------------------------------------------
        static::deleted(function (StockMovement $m) {
            DB::transaction(function () use ($m) {
                $delta = $m->type === self::TYPE_IN ? -$m->qty : +$m->qty; // revert

                $item = Item::whereKey($m->item_id)->lockForUpdate()->firstOrFail();

                if ($item->stock + $delta < 0) {
                    throw new InvalidArgumentException('Insufficient stock to revert.');
                }

                $item->increment('stock', $delta);
            });
        });
    }
}
