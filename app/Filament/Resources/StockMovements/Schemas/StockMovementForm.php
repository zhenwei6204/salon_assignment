<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\Item;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('item_id')
                ->label('Item')
                ->options(Item::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->required(),

            Select::make('type')
                ->options([
                    'in'  => 'IN',
                    'out' => 'OUT',
                ])
                ->required(),

            // integer-only inventory
            TextInput::make('qty')
                ->label('Quantity')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->required(),

            Textarea::make('reason')
                ->rows(2),
        ])->columns(2);
    }
}
