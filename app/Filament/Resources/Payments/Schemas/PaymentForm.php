<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

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

                // Booking ID remains a selectable dropdown
                Select::make('booking_id')
                    ->relationship('booking', 'customer_name')
                    ->searchable()
                    ->required()
                    ->label('Booking (Customer)'),

                // Amount remains an editable text input
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->prefix('MYR'),

                // Payment method remains a selectable dropdown
                Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                    ])
                    ->required(),

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