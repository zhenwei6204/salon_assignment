<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => strtoupper($state))
                    ->color(fn(string $state) => $state === 'in' ? 'success' : 'danger'),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignRight(),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->wrap()
                    ->limit(80),
                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('Y-m-d H:i')   
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->diffForHumans()),
                
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
