<?php

namespace App\Filament\Resources\Items\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

// Keep these the same style as your UsersTable:
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

// Row delete must come from Tables\Actions:
use Filament\Actions\DeleteAction;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use App\Models\StockMovement;
use App\Models\Item;
use Filament\Actions\Action;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->color(fn(int $state) => $state < 5 ? 'danger' : 'gray')
                    ->tooltip(fn(int $state) => $state < 5 ? 'Low stock' : 'In stock'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable()
                    ->tooltip(fn($record) => $record->updated_at?->diffForHumans()),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->tooltip(fn($record) => $record->updated_at?->diffForHumans()),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                EditAction::make(),

                Action::make('addStock')
                    ->label('Add stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason (optional)')
                            ->rows(2),
                    ])
                    ->action(function (Item $record, array $data) {
                        // Create movement; StockMovement::booted() will adjust stock
                        StockMovement::create([
                            'item_id' => $record->id,
                            'type' => 'in',
                            'qty' => (int) $data['qty'],   // << use qty
                            'reason' => $data['reason'] ?? 'Add Stock',
                            'user_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Stock added')
                            ->success()
                            ->send();
                    }),

                Action::make('deductStock')
                    ->label('Deduct stock')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->placeholder('e.g. expired, damaged')
                            ->rows(2)
                            ->required(),
                    ])
                    ->action(function (Item $record, array $data) {
                        $qty = (int) $data['qty'];

                        if ($qty > $record->stock) {
                            throw ValidationException::withMessages([
                                'qty' => "Cannot deduct more than current stock ({$record->stock}).",
                            ]);
                        }

                        StockMovement::create([
                            'item_id' => $record->id,
                            'type' => 'out',
                            'qty' => $qty,                 // << use qty
                            'reason' => $data['reason'],
                            'user_id' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Stock deducted')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);     
    }
}
