<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
 
    public function deductForBooking(Booking $booking): void
    {
        if ($booking->inventory_deducted_at) {
            return; 
        }

        DB::transaction(function () use ($booking) {
          
            $booking->loadMissing('service.consumedItems');

            foreach ($booking->service->consumedItems as $item) {
                $qty = (int) $item->pivot->qty_per_service;

                StockMovement::create([
                    'item_id'    => $item->id,
                    'booking_id' => $booking->id,
                    'type'       => StockMovement::TYPE_OUT, // 'out'
                    'qty'        => $qty,
                    'reason'     => "Service consumption for booking #{$booking->id}",
                    'source'     => 'booking',
                    'user_id'    => auth()->id(),
                ]);
            }

            $booking->forceFill([
                'inventory_deducted_at' => now(),
                'inventory_reverted_at' => null,
            ])->save();
        });
    }


    public function revertForBooking(Booking $booking): void
    {
        if (! $booking->inventory_deducted_at || $booking->inventory_reverted_at) {
            return; 
        }

        DB::transaction(function () use ($booking) {
            $booking->loadMissing('service.consumedItems');

            foreach ($booking->service->consumedItems as $item) {
                $qty = (int) $item->pivot->qty_per_service;

                StockMovement::create([
                    'item_id'    => $item->id,
                    'booking_id' => $booking->id,
                    'type'       => StockMovement::TYPE_IN, 
                    'qty'        => $qty,
                    'reason'     => "Booking #{$booking->id} cancelled, stock returned",
                    'source'     => 'booking',
                    'user_id'    => auth()->id(),
                ]);
            }

            $booking->forceFill([
                'inventory_reverted_at' => now(),
            ])->save();
        });
    }
}
