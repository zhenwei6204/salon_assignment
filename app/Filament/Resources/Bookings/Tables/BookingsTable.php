<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Mail\BookingConfirmationMail;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // No. (row index)
                TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex()
                    ->alignCenter()
                    ->sortable(false),

                // ✅ Booking reference (visible, searchable, copyable)
                TextColumn::make('booking_reference')
                    ->label('Booking Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    // Nice badge-ish look
                    ->formatStateUsing(fn ($state) => $state ?: '—')
                    ->extraAttributes(['class' => 'font-mono text-xs']),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('stylist.name')
                    ->label('Stylist')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('customer_email')
                    ->label('Email')
                    ->toggleable(),

                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->toggleable(),

                TextColumn::make('booking_date')
                    ->label('Booking date')
                    ->date('F j, Y')
                    ->sortable(),

                // Safely format time even if saved as H:i or H:i:s
                TextColumn::make('booking_time')
                    ->label('Booking time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state)),

                TextColumn::make('end_time')
                    ->label('End time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state)),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('myr', true)
                    ->sortable(),

                // Your status colors
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'booked',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
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
                SelectFilter::make('service')
                    ->relationship('service', 'name'),

                SelectFilter::make('stylist')
                    ->relationship('stylist', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'booked'    => 'Booked',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('booking_date', 'desc')
            ->recordActions([
                EditAction::make(),

                Action::make('resendConfirmation')
                    ->label('Resend email')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->load(['service', 'stylist']);
                        if ($record->customer_email) {
                            Mail::to($record->customer_email)
                                ->send(new BookingConfirmationMail($record));
                        }
                    })
                    ->successNotificationTitle('Confirmation email sent'),
            ]);
    }

    /**
     * Try to render a DB time column as "g:i A".
     * Accepts values like "09:00" or "09:00:00".
     */
    private static function formatTime(?string $value): string
    {
        if (blank($value)) {
            return '—';
        }

        // Try H:i:s first, then H:i
        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('g:i A');
            } catch (\Throwable $e) {
                // try next
            }
        }

        // fallback: show raw value
        return $value;
    }
}
