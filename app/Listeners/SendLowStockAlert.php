<?php

namespace App\Listeners;

use App\Events\StockAdjusted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendLowStockAlert
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockAdjusted $event): void
    {
        $item = $event->item->fresh();

        if ($item->onHand() <= $item->reorder_level) {
            
            Log::warning("Low stock: {$item->sku} ({$item->name}) — on hand = {$item->onHand()}");
        }
    }
}
