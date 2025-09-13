<?php

namespace App\Filament\Resources\Refunds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Booking;
use App\Models\Payment;
use Livewire\Component as LivewireComponent;
use App\Payments\PaymentContext;
class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Refund Reference (auto-generated)
                TextInput::make('refund_reference')
                    ->label('Refund Reference')
                    ->required()
                    ->default(fn () => 'REF-' . date('Ymd') . '-' . strtoupper(\Str::random(5)))
                    ->readOnly(),

                // Payment selection - this will trigger auto-fill
                Select::make('payment_id')
                    ->label('Payment')
                    ->options(function () {
                        return Payment::with('booking')
                            ->get()
                            ->mapWithKeys(function ($payment) {
                                $bookingRef = $payment->booking ? $payment->booking->booking_reference : 'No Booking';
                                $customer = $payment->booking ? $payment->booking->customer_name : 'Unknown';
                                return [$payment->id => "Payment #{$payment->id} - {$bookingRef} - {$customer} - RM{$payment->amount}"];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $payment = Payment::with('booking')->find($state);
                            if ($payment && $payment->booking) {
                                $booking = $payment->booking;
                                
                                // Auto-fill booking info
                                $set('booking_id', $booking->id);
                                $set('original_amount', $payment->amount);
                                $set('refund_amount', $payment->amount); // Default to full refund
                            }
                        } else {
                            // Clear fields if no payment selected
                            $set('booking_id', null);
                            $set('original_amount', null);
                            $set('refund_amount', null);
                        }
                    }),



      

                // Original amount (auto-populated from payment)
                TextInput::make('original_amount')
                    ->label('Original Amount')
                    ->numeric()
                    ->prefix('RM')
                    ->disabled() // Read-only
                    ->dehydrated(true), // Still save the value

                // Refund amount
                TextInput::make('refund_amount')
                    ->label('Refund Amount')
                    ->numeric()
                    ->required()
                    ->prefix('RM')
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $originalAmount = (float) $get('original_amount');
                                $refundAmount = (float) $value;
                                
                                if ($refundAmount <= 0) {
                                    $fail('Refund amount must be greater than zero.');
                                }
                                
                                if ($refundAmount > $originalAmount) {
                                    $fail('Refund amount cannot exceed the original payment amount.');
                                }
                            };
                        },
                    ]),

                // Refund type
                Select::make('refund_type')
                    ->options([
                        'full' => 'Full Refund',
                        'partial' => 'Partial Refund',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, callable $set) {
                        if ($state === 'full') {
                            $originalAmount = $get('original_amount');
                            if ($originalAmount) {
                                $set('refund_amount', $originalAmount);
                            }
                        }
                    }),

                // Status
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),

             Select::make('refund_method')
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
                        

                // Reason for refund
                Textarea::make('reason')
                    ->label('Refund Reason')
                    ->required()
                    ->rows(3),

                // Admin notes
                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->rows(3),

                // Requested by (hidden, auto-filled)
                TextInput::make('requested_by')
                    ->label('Requested By (User ID)')
                    ->numeric()
                    ->default(auth()->id())
                    ->disabled(),

                // Approved by
                TextInput::make('approved_by')
                    ->label('Approved By (User ID)')
                    ->numeric(),

                // Timestamps
                DateTimePicker::make('requested_at')
                    ->label('Requested At')
                    ->default(now()),

                DateTimePicker::make('approved_at')
                    ->label('Approved At'),

                DateTimePicker::make('processed_at')
                    ->label('Processed At'),

                DateTimePicker::make('completed_at')
                    ->label('Completed At'),

                DateTimePicker::make('created_at')
                    ->label('Created On'),

                DateTimePicker::make('updated_at')
                    ->label('Last Updated'),
            ])->columns(2);
    }
}