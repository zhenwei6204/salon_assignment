<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        DB::afterCommit(function () use ($item) {
            StockMovement::create([
                'item_id'   => $item->id,
                'item_name' => $item->name,
                'type'      => StockMovement::TYPE_IN,
                'qty'       => (int)($item->stock ?? 0),
                'reason'    => 'New Item',
                'source'    => 'opening_balance',  
                'user_id'   => auth()->id(),
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
        DB::afterCommit(function () use ($item) {
            StockMovement::create([
                'item_id' => $item->id,               
                'item_name' => $item->name,            
                'type' => StockMovement::TYPE_OUT,
                'qty'  => (int)($item->getOriginal('stock') ?? 0),
                'reason' => 'Item deleted',
                'source' => 'item_deleted',             
                'user_id' => auth()->id(),
            ]);
        });
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
