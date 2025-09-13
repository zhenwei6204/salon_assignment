<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->maxLength(64)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('unit')
                ->placeholder('pcs / ml / g'),

            Select::make('tracking_type')
                ->label('Tracking')
                ->options([
                    'unit' => 'Whole units (pcs/box)',
                    'measure' => 'Measured (ml/g)',
                ])
                ->required()
                ->default('unit'),

            TextInput::make('reorder_level')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default(0),

            TextInput::make('additional_stock')
                ->label('Add Stock Quantity')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->default(null)
                ->dehydrated(false) // do not try to save to items table (temporary for testing)
                ->helperText('Enter how many new units arrived. Leave blank if none.')
                ->hiddenOn('create'),
        ])->columns(2);
    }
}

