<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryValuationReport extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Inventory Valuation (Stock × Price)';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Item::query()->select(['id','name','sku','stock','price'])->orderBy('name'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Item')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->toggleable(),
                Tables\Columns\TextColumn::make('stock')->label('On hand')->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Price')->money('MYR')->sortable(),
                Tables\Columns\TextColumn::make('valuation')
                    ->label('Value')
                    ->state(fn (Item $record): float => (float) $record->stock * (float) $record->price)
                    ->money('MYR'),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->action(function (): StreamedResponse {
                        $filename = 'inventory-valuation-' . now()->format('Ymd-His') . '.csv';
                        return response()->streamDownload(function () {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['Item','SKU','Stock','Price','Value']);
                            Item::orderBy('name')->chunk(200, function ($items) use ($out) {
                                foreach ($items as $it) {
                                    fputcsv($out, [
                                        $it->name,
                                        $it->sku,
                                        $it->stock,
                                        number_format((float) $it->price, 2, '.', ''),
                                        number_format((float) $it->stock * (float) $it->price, 2, '.', ''),
                                    ]);
                                }
                            });
                            fclose($out);
                        }, $filename, ['Content-Type' => 'text/csv']);
                    }),
            ]);
    }
}
