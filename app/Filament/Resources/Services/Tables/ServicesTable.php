<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;  // Make sure to import TextColumn

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Display the service name
                TextColumn::make('name')->sortable()->searchable(),
                // Display price with currency formatting
                TextColumn::make('price')->sortable()->money('MYR'),
                // Show category name from related category table
                TextColumn::make('category.name')->label('Category')->sortable(),
                // Show creation date
                TextColumn::make('created_at')->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->recordActions([
                EditAction::make(), // Allow editing services
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(), // Allow deleting multiple services
                ]),
            ]);
    }
}
