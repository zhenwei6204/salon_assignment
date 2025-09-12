<?php
namespace App\Observers;

use App\Mail\LowStockAlert;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class StockMovementObserver
{
    // Hardcoded admin email(s) per your requirement (no env / config).
    private const ADMIN_EMAILS = ['darentester123@gmail.com'];

    /** Before save: snapshot/hide names for audit rows */
    public function creating(StockMovement $m): void
    {
        $src = $m->source ?? '';

        if ($src === 'item_deleted') {
            // Force no relation and no name for "deleted" audit rows
            $m->item_id   = null;
            $m->item_name = null;
            return;
        }

        // Snapshot item name if not provided
        if (!$m->item_name && $m->item_id) {
            if ($item = Item::find($m->item_id)) {
                $m->item_name = $item->name;
            }
        }
    }

    public function created(StockMovement $m): void
    {
        if (!$this->shouldApply($m)) return;

        DB::transaction(function () use ($m) {
            $item = Item::whereKey($m->item_id)->lockForUpdate()->first();
            if (!$item) return;

            $prev   = $item->stock;
            $delta  = $m->type === StockMovement::TYPE_IN ? +$m->qty : -$m->qty;

            if ($prev + $delta < 0) {
                throw new InvalidArgumentException('Insufficient stock.');
            }

            $item->increment('stock', $delta);
            $item->refresh();

            $this->checkThresholdAndNotify($item, $prev);
        });
    }

    public function updated(StockMovement $m): void
    {
        if (!$this->shouldApply($m)) return;

        DB::transaction(function () use ($m) {

            // Item switched
            if ($m->wasChanged('item_id')) {
                $oldItemId = $m->getOriginal('item_id');
                $oldType   = $m->getOriginal('type');
                $oldQty    = (int) $m->getOriginal('qty');

                $oldDelta  = $oldType === StockMovement::TYPE_IN ? +$oldQty : -$oldQty;
                $newDelta  = $m->type   === StockMovement::TYPE_IN ? +$m->qty  : -$m->qty;

                $oldItem = $oldItemId ? Item::whereKey($oldItemId)->lockForUpdate()->first() : null;
                $newItem = $m->item_id ? Item::whereKey($m->item_id)->lockForUpdate()->first() : null;

                if ($oldItem) {
                    $prev = $oldItem->stock;
                    if ($prev - $oldDelta < 0) {
                        throw new InvalidArgumentException('Cannot revert stock on old item below zero.');
                    }
                    $oldItem->increment('stock', -$oldDelta);
                    $oldItem->refresh();
                    $this->checkThresholdAndNotify($oldItem, $prev);
                }

                if ($newItem) {
                    $prev = $newItem->stock;
                    if ($prev + $newDelta < 0) {
                        throw new InvalidArgumentException('Insufficient stock on new item.');
                    }
                    $newItem->increment('stock', $newDelta);
                    $newItem->refresh();
                    $this->checkThresholdAndNotify($newItem, $prev);
                }

                return;
            }

            // Same item; type/qty changed
            $item = $m->item_id ? Item::whereKey($m->item_id)->lockForUpdate()->first() : null;
            if (!$item) return;

            $prev     = $item->stock;
            $oldDelta = ($m->getOriginal('type') === StockMovement::TYPE_IN ? +$m->getOriginal('qty') : -$m->getOriginal('qty'));
            $newDelta = ($m->type === StockMovement::TYPE_IN ? +$m->qty : -$m->qty);
            $diff     = $newDelta - $oldDelta;

            if ($prev + $diff < 0) {
                throw new InvalidArgumentException('Insufficient stock.');
            }

            $item->increment('stock', $diff);
            $item->refresh();

            $this->checkThresholdAndNotify($item, $prev);
        });
    }

    public function deleted(StockMovement $m): void
    {
        if (!$this->shouldApply($m)) return;

        DB::transaction(function () use ($m) {
            $item = $m->item_id ? Item::whereKey($m->item_id)->lockForUpdate()->first() : null;
            if (!$item) return;

            $prev  = $item->stock;
            $delta = $m->type === StockMovement::TYPE_IN ? -$m->qty : +$m->qty; // revert movement

            if ($prev + $delta < 0) {
                throw new InvalidArgumentException('Insufficient stock to revert.');
            }

            $item->increment('stock', $delta);
            $item->refresh();

            $this->checkThresholdAndNotify($item, $prev);
        });
    }

    /** Helpers *************************************************************/

    private function shouldApply(StockMovement $m): bool
    {
        if (!$m->item_id) return false; // audit-only (like item_deleted)
        $src = $m->source ?? '';
        // Do not apply stock math for audit rows
        if (in_array($src, ['item_deleted', 'opening_balance'])) return false;
        return true;
    }

    private function checkThresholdAndNotify(Item $item, int $previous): void
    {
        $threshold = (int) ($item->reorder_level ?? 5);

        // Crossing from >= threshold to < threshold → send once
        if ($previous >= $threshold && $item->stock < $threshold && is_null($item->low_stock_notified_at)) {
            Mail::to(self::ADMIN_EMAILS)->send(new LowStockAlert($item));
            $item->forceFill(['low_stock_notified_at' => now()])->saveQuietly();
            return;
        }

        // Recovered from < threshold back to >= threshold → clear flag
        if ($item->stock >= $threshold && $item->low_stock_notified_at) {
            $item->forceFill(['low_stock_notified_at' => null])->saveQuietly();
        }
    }
}
