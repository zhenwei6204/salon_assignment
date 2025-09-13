<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Service;
use App\Models\Stylist;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->schema([

            // Store current record ID (so we can safely ignore it in unique rules)
            Hidden::make('recordId')
                ->dehydrated(false)
                ->afterStateHydrated(function ($set, $state, $record) {
                    $set('recordId', $record?->getKey());
                }),

            // ──────── Booking reference (auto, unique, copyable) ──────────────────────────────────────
            TextInput::make('booking_reference')
                ->label('Booking Ref')
                ->helperText('Auto-generated on create')
                ->default(function ($record) {
                    // Only generate new reference if no record exists (create mode)
                    if (!$record) {
                        return self::makeBookingRef();
                    }
                    return $record->booking_reference;
                })
                ->readOnly()
                ->copyable()
                ->dehydrated(true)
                ->rule(function (callable $get) {
                    return Rule::unique('bookings', 'booking_reference')
                        ->ignore($get('recordId'));
                }),

            // ──────── Service ─────────────────────────────────────────────────────────────────────────
            Select::make('service_id')
                ->label('Service')
                ->relationship('service', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($get, $set) {
                    // reset time fields when service changes
                    $set('booking_time', null);
                    $set('end_time', null);

                    // price snapshot from selected service
                    $price = Service::find($get('service_id'))?->price;
                    if (!is_null($price)) {
                        $set('total_price', number_format((float) $price, 2, '.', ''));
                    }
                })
                ->native(false),

            // ──────── Stylist (capability check ONLY) ────────────────────────────────────────────────
            Select::make('stylist_id')
                ->label('Stylist')
                ->relationship('stylist', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->native(false)
                ->rule(function (callable $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $serviceId = $get('service_id');
                        if (!$serviceId || !$value)
                            return;

                        $service = Service::find($serviceId);
                        $stylist = Stylist::find($value);
                        if (!$service || !$stylist)
                            return;

                        // A) pivot relationship stylists<->services
                        if (method_exists($stylist, 'services')) {
                            if (!$stylist->services()->whereKey($service->getKey())->exists()) {
                                $fail("The selected stylist cannot perform the {$service->name} service.");
                            }
                            return;
                        }

                        // B) fallback: free-text / JSON specializations column
                        $spec = (string) ($stylist->specializations ?? '');
                        if ($spec === '' || mb_stripos($spec, $service->name) === false) {
                            $fail("The selected stylist cannot perform the {$service->name} service.");
                        }
                    };
                }),

            // ──────── User Selection (Customer Name Only) ────────────────────────────────────────────
            Select::make('user_id')
                ->label('Customer')
                ->relationship('user', 'name')
                ->searchable(['name', 'email'])
                ->preload()
                ->required()
                ->native(false)
                ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->name)
                ->reactive()
                ->afterStateUpdated(function ($get, $set, $state) {
                    // Auto-fill customer fields when user is selected
                    if ($state) {
                        $user = User::find($state);
                        if ($user) {
                            $set('customer_name', $user->name);
                            $set('customer_email', $user->email);
                            $set('customer_phone', $user->phone ?? '');
                        }
                    } else {
                        // Clear customer fields if user is deselected
                        $set('customer_name', '');
                        $set('customer_email', '');
                        $set('customer_phone', '');
                    }
                })
                ->createOptionForm([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique('users', 'email')
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->maxLength(20),
                ])
                ->createOptionAction(function ($action) {
                    return $action
                        ->modalHeading('Create New Customer')
                        ->modalSubmitActionLabel('Create Customer')
                        ->modalWidth('lg');
                }),

            // ──────── Auto-filled fields ─────────────────────────────────────────────────────────────
            Hidden::make('customer_name')
                ->dehydrated(true),

            TextInput::make('customer_email')
                ->label('Email')
                ->disabled()
                ->dehydrated(true),

            // FIXED: Phone field is now editable but auto-filled
            TextInput::make('customer_phone')
                ->label('Phone')
                ->dehydrated(true)
                ->maxLength(20)
                ->reactive()
                ->afterStateHydrated(function ($set, $state, $record) {
                    // When editing existing record, show the stored phone
                    if ($record && $record->customer_phone) {
                        $set('customer_phone', $record->customer_phone);
                    }
                }),

            TextInput::make('payment_id')
                ->label('Payment ID')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('No payment record')
                ->helperText('Reference to payment record')
                ->afterStateHydrated(function ($set, $record) {
                    if ($record && $record->payment_id) {
                        $set('payment_id', $record->payment_id);
                    }
            }),

            Textarea::make('special_requests')
                ->label('Special requests')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),

            // ──────── Date ────────────────────────────────────────────────────────────────────────────
            DatePicker::make('booking_date')
                ->label('Booking date')
                ->required()
                ->reactive()
                ->minDate(now()->toDateString()) // Prevent booking past dates
                ->afterStateUpdated(function ($set) {
                    $set('booking_time', null);
                    $set('end_time', null);
                }),

            // ──────── Booking time (allowed options + overlap protection) ────────────────────────────
            Select::make('booking_time')
                ->label('Booking time')
                ->reactive()
                ->options(fn($get) => self::slotOptionsForService($get('service_id'), $get('booking_date')))
                ->required()
                ->native(false)
                ->placeholder('Select a time slot')
                ->disabled(fn ($get) => is_null($get('service_id')) || is_null($get('booking_date')) || empty(self::slotOptionsForService($get('service_id'), $get('booking_date'))))
                // When loading existing record, convert DB time to H:i format for the select
                ->afterStateHydrated(function ($set, $state, $record) {
                    if ($state && $record) {
                        $timeString = self::extractTimeFromValue($state);
                        $set('booking_time', $timeString);
                    }
                })
                // When saving, convert H:i format to H:i:s for database
                ->dehydrateStateUsing(fn ($state) => $state ? self::formatTimeForDatabase($state) : null)
                // Auto-set end_time when booking_time changes
                ->afterStateUpdated(function ($get, $set, $state) {
                    if ($state) {
                        $mins = self::serviceDurationMinutes($get('service_id'));
                        if ($mins) {
                            try {
                                $endTime = Carbon::createFromFormat('H:i', $state)->addMinutes($mins);
                                $set('end_time', $endTime->format('H:i'));
                            } catch (\Exception $e) {
                                // Handle error gracefully
                                $set('end_time', null);
                            }
                        }
                    } else {
                        $set('end_time', null);
                    }
                })
                // Validation rules
                ->rule(function (callable $get) {
                    return Rule::in(array_keys(self::slotOptionsForService($get('service_id'), $get('booking_date'))));
                })
                ->rule(function (callable $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $stylistId = $get('stylist_id');
                        $date = $get('booking_date');
                        $serviceId = $get('service_id');
                        if (!$stylistId || !$date || !$value || !$serviceId)
                            return;

                        $start = Carbon::createFromFormat('H:i', $value);
                        $end = $start->copy()->addMinutes(self::serviceDurationMinutes($serviceId));
                        $recordId = $get('recordId') ?? null;

                        $exists = Booking::query()
                            ->where('stylist_id', $stylistId)
                            ->whereDate('booking_date', $date)
                            ->whereRaw("TIME(booking_time) < ?", [$end->format('H:i:s')])
                            ->whereRaw("TIME(end_time)      > ?", [$start->format('H:i:s')])
                            ->when($recordId, fn($q) => $q->whereKeyNot($recordId))
                            ->exists();

                        if ($exists) {
                            $fail('This slot overlaps with another booking for this stylist.');
                        }
                    };
                })
                ->rule(function (callable $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $bookingDate = $get('booking_date');
                        if (!$bookingDate || !$value)
                            return;

                        $selectedDate = Carbon::parse($bookingDate);
                        
                        if ($selectedDate->isToday()) {
                            $now = Carbon::now();
                            $selectedDateTime = $selectedDate->copy()->setTimeFromTimeString($value . ':00');

                            if ($selectedDateTime->isPast()) {
                                $fail('Cannot book a time slot that has already passed.');
                            }
                        }
                    };
                }),

            // ──────── End time (auto-calculated, read-only display) ──────────────────────────────────
            TextInput::make('end_time')
                ->label('End time')
                ->disabled()
                ->dehydrated(true)
                ->placeholder('Will be calculated automatically')
                // Format for display when loading existing record
                ->afterStateHydrated(function ($set, $state, $record) {
                    if ($state && $record) {
                        $timeString = self::extractTimeFromValue($state);
                        // Format as readable time for display
                        try {
                            $displayTime = Carbon::createFromFormat('H:i', $timeString)->format('g:i A');
                            $set('end_time', $displayTime);
                        } catch (\Exception $e) {
                            $set('end_time', $timeString);
                        }
                    }
                })
                // Convert display format back to H:i:s for database
                ->dehydrateStateUsing(function ($state) {
                    if (!$state) return null;
                    
                    try {
                        // If it's in display format (g:i A), convert to H:i:s
                        if (preg_match('/^\d{1,2}:\d{2}\s+(AM|PM)$/i', $state)) {
                            return Carbon::createFromFormat('g:i A', $state)->format('H:i:s');
                        }
                        // If it's H:i format, convert to H:i:s
                        elseif (preg_match('/^\d{2}:\d{2}$/', $state)) {
                            return Carbon::createFromFormat('H:i', $state)->format('H:i:s');
                        }
                        // If it's already H:i:s, return as is
                        else {
                            return $state;
                        }
                    } catch (\Exception $e) {
                        return null;
                    }
                }),

            // ──────── Pricing + Status ───────────────────────────────────────────────────────────────
            TextInput::make('total_price')
                ->label('Total price')
                ->numeric()
                ->step('0.01')
                ->disabled()
                ->dehydrated(true),

            Select::make('status')
                ->label('Status')
                ->options([
                    'booked' => 'Booked',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])
                ->default('booked')
                ->required(),
        ]);
    }

    /** Extract time from various formats and return H:i format */
    protected static function extractTimeFromValue($value): string
    {
        if (!$value) return '';

        // Handle full datetime strings like "2025-09-11T02:00:00.000000Z"
        if (preg_match('/\d{4}-\d{2}-\d{2}[T\s](\d{2}:\d{2})/', $value, $matches)) {
            return $matches[1];
        }

        // Handle H:i:s format
        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $value, $matches)) {
            return $matches[1];
        }

        // Handle H:i format
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        // Try to parse with Carbon as last resort
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '';
        }
    }

    /** Format time for database storage (H:i:s) */
    protected static function formatTimeForDatabase($timeValue): string
    {
        if (!$timeValue) return '';

        try {
            // If it's H:i format, convert to H:i:s
            if (preg_match('/^\d{2}:\d{2}$/', $timeValue)) {
                return $timeValue . ':00';
            }
            
            // If it's already H:i:s, return as is
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeValue)) {
                return $timeValue;
            }

            // Try parsing with Carbon
            return Carbon::parse($timeValue)->format('H:i:s');
        } catch (\Exception $e) {
            return $timeValue;
        }
    }

    /** Generate a unique booking reference like "BKG-YYYYMMDD-ABCDE". */
    public static function makeBookingRef(): string
    {
        do {
            $ref = 'BKG-' . now()->format('Ymd') . '-' . strtoupper(str()->random(5));
        } while (Booking::where('booking_reference', $ref)->exists());

        return $ref;
    }

    /** Allowed START slots for a service as ['H:i' => 'g:i A']. */
    protected static function slotOptionsForService($serviceId, ?string $bookingDate): array
    {
        if (is_null($serviceId) || is_null($bookingDate)) {
            return [];
        }

        $slots = self::defaultStartSlotKeys();

        if ($serviceId) {
            $service = Service::find($serviceId);
            if ($service && !empty($service->allowed_slots)) {
                $raw = is_array($service->allowed_slots)
                    ? $service->allowed_slots
                    : (self::looksLikeJson($service->allowed_slots)
                        ? (json_decode($service->allowed_slots, true) ?: [])
                        : explode(',', (string) $service->allowed_slots));

                $slots = array_values(array_filter(
                    array_map(fn($v) => trim((string) $v), $raw),
                    fn($h) => preg_match('/^\d{2}:\d{2}$/', $h)
                ));
            }
        }

        $out = [];
        if ($bookingDate && Carbon::parse($bookingDate)->isToday()) {
            $now = Carbon::now();
            foreach ($slots as $h_i) {
                try {
                    $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', $bookingDate . ' ' . $h_i);
                    if ($slotDateTime->gt($now->copy()->addMinutes(30))) {
                        $out[$h_i] = Carbon::createFromFormat('H:i', $h_i)->format('g:i A');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } else {
            foreach ($slots as $h_i) {
                try {
                    $out[$h_i] = Carbon::createFromFormat('H:i', $h_i)->format('g:i A');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        ksort($out);
        return $out;
    }

    /** Default START slot keys 09:00 – 16:00, every 30 minutes. */
    protected static function defaultStartSlotKeys(): array
    {
        $start = Carbon::createFromTime(9, 0);
        $end = Carbon::createFromTime(16, 0);
        $step = 30;

        $slots = [];
        for ($t = $start->copy(); $t->lte($end); $t->addMinutes($step)) {
            $slots[] = $t->format('H:i');
        }
        return $slots;
    }

    /** Service duration in minutes */
    protected static function serviceDurationMinutes($serviceId): int
    {
        $s = $serviceId ? Service::find($serviceId) : null;
        $mins = (int) ($s->duration_minutes ?? $s->duration ?? 0);
        return $mins > 0 ? $mins : 60;
    }

    protected static function looksLikeJson(string $s): bool
    {
        $s = trim($s);
        return $s !== '' && (
            ($s[0] === '[' && substr($s, -1) === ']') ||
            ($s[0] === '{' && substr($s, -1) === '}')
        );
    }
}