<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ConsumedItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'consumedItems';  

    protected static ?string $title = 'Consumed Items';

    protected static ?string $recordTitleAttribute = 'name';

   public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Item')->searchable(),
                TextColumn::make('pivot.qty_per_service')->label('Qty per service')->sortable(),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'sku'])
                    ->recordTitleAttribute('name')
                    // IMPORTANT: include the record select in the form
                    ->form(fn (AttachAction $action) => [
                        $action->getRecordSelect()->label('Item')->required(),
                        TextInput::make('qty_per_service')
                            ->label('Qty per service')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
            ])
            ->actions([
                EditAction::make()->form([
                    TextInput::make('pivot.qty_per_service')
                        ->label('Qty per service')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}
