<?php

namespace App\Listeners;

use App\Events\StockAdjusted;
use Illuminate\Support\Facades\Log;

class CheckReorderLevel
{
    public function handle(StockAdjusted $e): void
    {
        $onHand = $e->item->onHand();

        if ($onHand <= $e->item->reorder_level) {
            // send a notification, email, or just log for now
            Log::warning("Low stock for {$e->item->sku} ({$e->item->name}). On hand: {$onHand}.");
            // e.g. dispatch a Notification here
        }
    }
}

