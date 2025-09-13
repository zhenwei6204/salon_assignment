<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopUsedItemsTable extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Top 5 Most Used Items (30d)';
    }

    public function table(Table $table): Table
    {
        $query = Item::query()
            ->select('items.id', 'items.name', 'items.price')
            ->selectSub(function ($q) {
                $q->from('stock_movements as sm')
                  ->selectRaw('COALESCE(SUM(CASE WHEN sm.type = "OUT" THEN sm.qty ELSE 0 END), 0)')
                  ->whereColumn('sm.item_id', 'items.id')
                  ->where('sm.created_at', '>=', now()->subDays(30));
            }, 'used_qty_30d')
            ->orderByDesc('used_qty_30d')
            ->limit(5);

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Item')->wrap(),
                Tables\Columns\TextColumn::make('used_qty_30d')
                    ->label('Used Qty (30d)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('used_value')
                    ->label('Used Value')
                    ->state(fn ($record) => (float) $record->used_qty_30d * (float) $record->price)
                    ->money('MYR'),
            ]);
    }
}
