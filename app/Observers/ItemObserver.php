<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockMovement;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        
        $qty = (int) $item->stock;
        if ($qty <= 0) {
            return;
        }

        StockMovement::withoutEvents(function () use ($item, $qty) {
            StockMovement::create([
                'item_id' => $item->id,
                'type'    => 'in',
                'qty'     => $qty,
                'reason'  => 'New Item',
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}
