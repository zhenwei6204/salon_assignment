<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryValueStat extends BaseWidget
{
   protected function getHeading(): ?string
    {
        return 'Inventory Value';
    }

    protected function getStats(): array
    {
        $totalValue = Item::query()->selectRaw('SUM(stock * price) as v')->value('v') ?? 0;
        $avgPrice   = Item::query()->avg('price') ?? 0;
        $skuCount   = Item::query()->count();

        return [
            Stat::make('Total value', 'RM ' . number_format($totalValue, 2)),
            Stat::make('Avg item price', 'RM ' . number_format($avgPrice, 2)),
            Stat::make('SKUs', (string) $skuCount),
        ];
    }
}
