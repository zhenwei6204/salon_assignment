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

                // âœ… Booking reference (visible, searchable, copyable)
                TextColumn::make('booking_reference')
                    ->label('Booking Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    // Nice badge-ish look
                    ->formatStateUsing(fn ($state) => $state ?: 'â€”')
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
                
                TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? '#' . $state : '—')
                    ->extraAttributes(['class' => 'font-mono text-xs']),

                TextColumn::make('booking_date')
                    ->label('Booking date')
                    ->date('F j, Y')
                    ->sortable(),

                // Fixed time formatting - handle datetime strings properly
                TextColumn::make('booking_time')
                    ->label('Booking time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state))
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('End time')
                    ->formatStateUsing(fn ($state) => self::formatTime($state))
                    ->sortable(),

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
     * Enhanced time formatting to handle various time formats including datetime strings
     */
    private static function formatTime(?string $value): string
    {
        if (blank($value)) {
            return 'â€”';
        }

        // Handle datetime strings (e.g., "2025-09-11 09:30:00")
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+(\d{2}:\d{2}:\d{2})$/', $value, $matches)) {
            try {
                return Carbon::createFromFormat('H:i:s', $matches[1])->format('g:i A');
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Handle datetime strings with just H:i (e.g., "2025-09-11 09:30")
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s+(\d{2}:\d{2})$/', $value, $matches)) {
            try {
                return Carbon::createFromFormat('H:i', $matches[1])->format('g:i A');
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // Handle time-only formats
        $timeFormats = ['H:i:s', 'H:i'];
        foreach ($timeFormats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('g:i A');
            } catch (\Throwable $e) {
                // try next format
            }
        }

        // Last resort: try to parse as Carbon and extract time
        try {
            $carbon = Carbon::parse($value);
            return $carbon->format('g:i A');
        } catch (\Throwable $e) {
            // Return original value if all parsing fails
            return $value;
        }
    }
}