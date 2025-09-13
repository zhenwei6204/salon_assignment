<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Item;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
 


class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->wrap(),
                TextColumn::make('unit')->label('Unit'),
                TextColumn::make('tracking_type')->badge()->label('Tracking'),
                TextColumn::make('reorder_level')->label('Reorder')->sortable(),
                TextColumn::make('on_hand')
                    ->label('On hand')
                    ->state(fn (Item $record) => $record->onHand())
                    ->sortable()
                    ->badge()
                    ->color(fn (Item $record) => $record->onHand() < $record->reorder_level ? 'danger' : 'success'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}

