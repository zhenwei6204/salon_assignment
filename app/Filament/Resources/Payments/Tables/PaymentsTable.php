<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // The unique ID for the payment record itself
                TextColumn::make('id')
                    ->label('Payment ID')
                    ->sortable()
                    ->searchable(),

                // The name of the customer from the related booking
                TextColumn::make('booking.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // The payment amount
                TextColumn::make('amount')
                    ->sortable()
                    ->money('MYR'),

                // The method of payment
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->searchable(),

                // The current status of the payment
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ])
                    ->searchable(),

                // Timestamps, hidden by default
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
                //
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
