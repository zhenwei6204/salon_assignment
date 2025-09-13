<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use App\Models\Item;


class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Item name')
                ->required()
                ->maxLength(255),

            TextInput::make('sku')
                ->label('SKU')
                ->maxLength(100),

            TextInput::make('price')
                ->label('Price (RM)')
                ->prefix('RM')
                ->numeric()
                ->inputMode('decimal')
                ->minValue(0)
                ->step('0.01')
                ->required(),

            Select::make('unit')
                ->label('Unit')
                ->options([
                    'pcs' => 'pcs',
                    'box' => 'box',
                    'pack' => 'pack',
                    'bottle' => 'bottle',
                ])
                ->searchable()
                ->native(false)
                ->required()
                ->default('pcs'),

            TextInput::make('stock')
                ->label('Current stock')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->default(0)
                ->required()
                ->visibleOn('create'),

            Placeholder::make('current_stock')
                ->label('Current stock')
                ->content(fn(?Item $record) => $record?->stock)
                ->visibleOn('edit'),

            Textarea::make('notes')
                ->rows(2)
                ->label('Notes')
                ->maxLength(1000),
        ])->columns(2);
    }
}
