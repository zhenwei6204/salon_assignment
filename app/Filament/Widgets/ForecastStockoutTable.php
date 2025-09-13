<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ForecastStockoutTable extends BaseWidget
{
    protected function getHeading(): ?string { return 'Forecasted Stock-Outs (next 30d)'; }

    public function table(Table $table): Table
    {
        $from = now()->subDays(14);

        $query = Item::query()
            ->select('items.id','items.name','items.stock','items.price')
            ->selectSub(function ($q) use ($from) {
                $q->from('stock_movements as sm')
                  ->selectRaw('COALESCE(SUM(CASE WHEN sm.type = "OUT" THEN sm.qty ELSE 0 END), 0)')
                  ->whereColumn('sm.item_id', 'items.id')
                  ->where('sm.created_at', '>=', $from);
            }, 'out_14d')
            ->orderBy('stock');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Item')->searchable(),
                Tables\Columns\TextColumn::make('stock')->label('On hand')->numeric(),

                Tables\Columns\TextColumn::make('avg_daily_out')
                    ->label('Avg Daily OUT')
                    ->state(function ($r) {
                        $qty14 = (float) ($r?->out_14d ?? 0);
                        return round($qty14 / 14, 2);
                    }),

                Tables\Columns\TextColumn::make('days_to_stockout')
                    ->label('Days Left')
                    ->state(function ($r) {
                        $qty14 = (float) ($r?->out_14d ?? 0);
                        $avg   = $qty14 / 14;
                        $stock = (float) ($r?->stock ?? 0);
                        return $avg > 0 ? round($stock / $avg, 1) : '—';
                    })
                    ->badge()
                    ->color(fn ($state) =>
                        is_numeric($state)
                            ? ($state <= 7 ? 'danger' : ($state <= 14 ? 'warning' : 'success'))
                            : 'gray'
                    ),

                Tables\Columns\TextColumn::make('stockout_date')
                    ->label('Est. Stock-out')
                    ->state(function ($r) {
                        $qty14 = (float) ($r?->out_14d ?? 0);
                        $avg   = $qty14 / 14;
                        $stock = (float) ($r?->stock ?? 0);
                        return $avg > 0
                            ? now()->addDays($stock / $avg)->format('Y-m-d')
                            : '—';
                    }),
            ]);
    }
}
