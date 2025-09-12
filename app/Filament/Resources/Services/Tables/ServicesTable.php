<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Display the service name
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                // Display the service description, limit length and make it toggleable
                TextColumn::make('description')
                    ->limit(50)
                    ->tooltip('Click to see full description')
                    ->toggleable(),

                // Display the service benefits, limit length and make it toggleable
                TextColumn::make('benefits')
                    ->limit(50)
                    ->tooltip('Click to see full benefits')
                    ->toggleable(),

                // Display price with currency formatting
                TextColumn::make('price')
                    ->sortable()
                    ->money('MYR'),

                // Display the duration in minutes
                TextColumn::make('duration')
                    ->sortable()
                    ->label('Duration (mins)'),

                // Display the availability status with an icon
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean(),

                // Display stylist qualifications, limit length and make it toggleable
                TextColumn::make('stylist_qualifications')
                    ->limit(50)
                    ->tooltip('Click to see full qualifications')
                    ->toggleable(),

                // Display the image
                ImageColumn::make('image_url')
                    ->label('Image'),

                // Show category name from related category table
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                // Show creation and update dates, and hide by default
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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