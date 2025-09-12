<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /** Deduct items when a booking is completed */
    public function deductForBooking(Booking $booking): void
    {
        if ($booking->inventory_deducted_at) {
            return; // already processed
        }

        DB::transaction(function () use ($booking) {
            // Ensure relations are loaded
            $booking->loadMissing('service.consumedItems');

            foreach ($booking->service->consumedItems as $item) {
                $qty = (int) $item->pivot->qty_per_service;

                // (Optional proactive check; model will also enforce)
                // $locked = Item::whereKey($item->id)->lockForUpdate()->first();
                // if ($locked->stock - $qty < 0) {
                //     throw new InvalidArgumentException("Insufficient stock for {$item->name}.");
                // }

                // Do NOT decrement stock here — let StockMovement events handle stock math
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

    /** Revert items when a deducted booking is cancelled */
    public function revertForBooking(Booking $booking): void
    {
        if (! $booking->inventory_deducted_at || $booking->inventory_reverted_at) {
            return; // nothing to revert or already reverted
        }

        DB::transaction(function () use ($booking) {
            $booking->loadMissing('service.consumedItems');

            foreach ($booking->service->consumedItems as $item) {
                $qty = (int) $item->pivot->qty_per_service;

                // Do NOT increment stock here — let StockMovement events handle stock math
                StockMovement::create([
                    'item_id'    => $item->id,
                    'booking_id' => $booking->id,
                    'type'       => StockMovement::TYPE_IN, // 'in'
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
