<?php

// app/Filament/Resources/Stylists/Tables/StylistsTable.php

namespace App\Filament\Resources\Stylists\Tables;

use App\Models\Stylist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StylistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Stylist::with('services')) // Eager load the services relationship
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('experience_years')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                // Corrected column for assigned services
                TextColumn::make('services')
                    ->label('Assigned Services')
                    ->getStateUsing(function (Stylist $stylist) {
        // Ensure only unique services are displayed in the table
        return $stylist->services->unique('id')->pluck('name')->join(', ');
    })
    ->sortable(),
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
                // Filters can be added here
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}