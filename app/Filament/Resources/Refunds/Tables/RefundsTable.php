<?php

namespace App\Filament\Resources\Refunds\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Refund ID
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                // Refund reference
                TextColumn::make('refund_reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // Customer from booking
                TextColumn::make('booking.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // Booking reference
                TextColumn::make('booking.booking_reference')
                    ->label('Booking Ref')
                    ->searchable()
                    ->sortable(),

                // Payment ID
                TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Refund amount
                TextColumn::make('refund_amount')
                    ->label('Refund Amount')
                    ->sortable()
                    ->money('MYR'),

                // Original amount
                TextColumn::make('original_amount')
                    ->label('Original Amount')
                    ->sortable()
                    ->money('MYR'),

                // Refund type
                TextColumn::make('refund_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),

                // Status
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'rejected',
                    ])
                    ->searchable(),

                // Refund method
                TextColumn::make('refund_method')
                    ->label('Method')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst(str_replace('_', ' ', $state)) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Reason
                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Admin notes
                TextColumn::make('admin_notes')
                    ->label('Admin Notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Requested by
                TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Approved by
                TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Requested date
                TextColumn::make('requested_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),

                // Approved date
                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Processed date
                TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Completed date
                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Created/Updated timestamps
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
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('refund_type')
                    ->options([
                        'full' => 'Full',
                        'partial' => 'Partial',
                    ]),

                SelectFilter::make('refund_method')
                    ->options([
                        'original_payment_method' => 'Original Payment Method',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'store_credit' => 'Store Credit',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Refund approved')
                            ->success()
                            ->send();
                    }),

                Action::make('complete')
                    ->label('Complete')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'processing']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Refund completed')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => 'Rejected by admin',
                        ]);

                        Notification::make()
                            ->title('Refund rejected')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}