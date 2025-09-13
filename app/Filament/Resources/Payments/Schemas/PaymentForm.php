<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Booking;
use Livewire\Component as LivewireComponent;
use App\Payments\PaymentContext;
class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ID is now an editable text input
                TextInput::make('id')
                    ->label('Payment ID')
                    ->numeric()
                    ->required()
                    ->helperText('WARNING: Changing this can break database relationships.'),

               // Booking ID with proper handling for edit vs create
                Select::make('booking_id')
                    ->label('Booking (Customer)')
                    ->options(function (LivewireComponent $livewire = null) {
                        // Check if we're editing (record exists) or creating (no record)
                        $record = $livewire?->record ?? null;

                        $bookingQuery = Booking::with('user'); // Eager load the user relationship

                        if ($record && isset($record->id)) {
                            // When editing: include bookings without payment_id OR the current booking
                            $bookingQuery->where(function ($query) use ($record) {
                                $query->whereNull('payment_id')
                                      ->orWhere('id', $record->booking_id);
                            });
                        } else {
                            // When creating: only bookings without payment_id
                            $bookingQuery->whereNull('payment_id');
                        }
                        
                        // Get the bookings and format them for the dropdown
                        return $bookingQuery->get()->mapWithKeys(function ($booking) {
                            // Use the user's name for the label and the booking id for the value.
                            // The optional() helper prevents an error if a booking somehow has no user.
                            $userName = optional($booking->user)->name ?? 'Unknown User';
                            return [$booking->id => "{$userName} (Booking ID: {$booking->id})"];
                        });
                    })
                    ->searchable()
                    ->required(),

                // Amount remains an editable text input
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->prefix('MYR'),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options(function () {
                        $paymentContext = new PaymentContext();
                        $availableMethods = $paymentContext->getAvailablePaymentMethods();
                        
                        $options = [];
                        foreach ($availableMethods as $method) {
                            $options[$method['key']] = $method['name'];
                        }
                        
                        return $options;
                    })
                    ->required()
                    ->helperText('Payment methods are automatically loaded from available strategies'),


                // Status remains a selectable dropdown
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->required(),

                // Timestamps are now editable datetime pickers
                DateTimePicker::make('created_at')
                    ->label('Created On'),

                DateTimePicker::make('updated_at')
                    ->label('Last Updated'),
            ])->columns(2);
    }
}