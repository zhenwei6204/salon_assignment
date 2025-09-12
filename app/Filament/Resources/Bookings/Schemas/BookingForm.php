<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Service;
use App\Models\Stylist;
use App\Models\Booking;
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

            // ── Booking reference (auto, unique, copyable) ───────────────────
            TextInput::make('booking_reference')
                ->label('Booking Ref')
                ->helperText('Auto-generated on create')
                ->default(fn($record) => $record?->booking_reference ?? self::makeBookingRef())
                ->readOnly()
                ->copyable()
                ->dehydrated(true)
                ->rule(function (callable $get) {
                    return Rule::unique('bookings', 'booking_reference')
                        ->ignore($get('recordId'));
                })
                ->disabled(),

            // ── Service ────────────────────────────────────────────────────────
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

            // ── Stylist (capability check ONLY) ───────────────────────────────
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

            // ── Customer fields (required) ────────────────────────────────────
            TextInput::make('customer_name')
                ->label('Customer name')
                ->required()
                ->minLength(2)
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Customer name is required.',
                    'min' => 'Customer name must be at least :min characters.',
                    'max' => 'Customer name may not be greater than :max characters.',
                ]),

            TextInput::make('customer_email')
                ->label('Customer email')
                ->email()
                ->required()
                ->maxLength(255)
                ->validationMessages([
                    'required' => 'Customer email is required.',
                    'email' => 'Please enter a valid email address.',
                    'max' => 'Email may not be greater than :max characters.',
                ]),

            TextInput::make('customer_phone')
                ->label('Customer phone')
                ->maxLength(20),

            Textarea::make('special_requests')
                ->label('Special requests')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),

            // ── Date ───────────────────────────────────────────────────────────
            DatePicker::make('booking_date')
                ->label('Booking date')
                ->required()
                ->reactive()
                ->minDate(now()->toDateString()) // Prevent booking past dates
                ->afterStateUpdated(function ($set) {
                    $set('booking_time', null);
                    $set('end_time', null);
                }),

            // ── Booking time (allowed options + overlap protection) ───────────
            Select::make('booking_time')
                ->label('Booking time')
                ->reactive()
                ->options(fn($get) => self::slotOptionsForService($get('service_id'), $get('booking_date')))
                ->required()
                ->native(false)
                ->placeholder('Select a time slot')
                ->disabled(fn($get) => is_null($get('service_id')) || is_null($get('booking_date')) || empty(self::slotOptionsForService($get('service_id'), $get('booking_date'))))
                // Edit: DB 'H:i:s' -> select 'H:i'
                ->afterStateHydrated(function ($set, $state) {
                    if ($state) {
                        // Handle both H:i:s and H:i formats
                        $timeFormat = strlen($state) > 5 ? 'H:i:s' : 'H:i';
                        try {
                            $formatted = Carbon::createFromFormat($timeFormat, $state)->format('H:i');
                            $set('booking_time', $formatted);
                        } catch (\Exception $e) {
                            // If parsing fails, just use the first 5 characters
                            $set('booking_time', substr($state, 0, 5));
                        }
                    }
                })
                // Save: 'H:i' -> DB 'H:i:s'
                ->dehydrateStateUsing(fn($state) => $state ? Carbon::createFromFormat('H:i', $state)->format('H:i:s') : null)
                // Auto-set end_time from duration
                ->afterStateUpdated(function ($get, $set, $state) {
                    $mins = self::serviceDurationMinutes($get('service_id'));
                    if ($state && $mins) {
                        $end = Carbon::createFromFormat('H:i', $state)->addMinutes($mins)->format('H:i');
                        $set('end_time', $end);
                    }
                })
                // Only allow values present for this service
                ->rule(function (callable $get) {
                    return Rule::in(array_keys(self::slotOptionsForService($get('service_id'), $get('booking_date'))));
                })
                // Prevent stylist double-booking (ignore current record on edit)
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
                            // overlap: existing_start < new_end AND existing_end > new_start
                            ->whereRaw("TIME(booking_time) < ?", [$end->format('H:i:s')])
                            ->whereRaw("TIME(end_time)      > ?", [$start->format('H:i:s')])
                            ->when($recordId, fn($q) => $q->whereKeyNot($recordId))
                            ->exists();

                        if ($exists) {
                            $fail('This slot overlaps with another booking for this stylist.');
                        }
                    };
                })
                // Additional validation to prevent booking past times on today
                ->rule(function (callable $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                        $bookingDate = $get('booking_date');
                        if (!$bookingDate || !$value)
                            return;

                        $selectedDate = Carbon::parse($bookingDate);

                        // If booking is for today, check if the time has already passed
                        if ($selectedDate->isToday()) {
                            $now = Carbon::now();
                            $selectedDateTime = $selectedDate->copy()->setTimeFromTimeString($value . ':00');

                            if ($selectedDateTime->isPast()) {
                                $fail('Cannot book a time slot that has already passed.');
                            }
                        }
                    };
                }),

            // ── End time (derived: start slot + duration) ─────────────────────
            Select::make('end_time')
                ->label('End time')
                ->options(fn($get) => self::endSlotOptionsForService($get('service_id'), $get('booking_date')))
                ->native(false)
                ->disabled(),

            // ── Pricing + Status ───────────────────────────────────────────────
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
        // 🛠️ FIX: Add this check to prevent errors with null values
        if (is_null($serviceId) || is_null($bookingDate)) {
            return [];
        }

        $slots = self::defaultStartSlotKeys(); // Start with the default slots

        if ($serviceId) {
            $service = Service::find($serviceId);
            if ($service && !empty($service->allowed_slots)) {
                // Determine the raw slots from the service configuration
                $raw = is_array($service->allowed_slots)
                    ? $service->allowed_slots
                    : (self::looksLikeJson($service->allowed_slots)
                        ? (json_decode($service->allowed_slots, true) ?: [])
                        : explode(',', (string) $service->allowed_slots));

                // Filter and validate the raw slots
                $slots = array_values(array_filter(
                    array_map(fn($v) => trim((string) $v), $raw),
                    fn($h) => preg_match('/^\d{2}:\d{2}$/', $h)
                ));
            }
        }

     
        $out = [];

        if ($bookingDate) {
            
            $dateStr = is_string($bookingDate)
                ? substr($bookingDate, 0, 10)  
                : Carbon::parse($bookingDate)->toDateString();

            if (Carbon::parse($dateStr)->isToday()) {
                $now = Carbon::now();

                foreach ($slots as $h_i) {
                    try {
                        $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$dateStr} {$h_i}");

                    
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
        }

        ksort($out);
        return $out;
    }

    /** Allowed END slots = each start slot + service duration. */
    protected static function endSlotOptionsForService($serviceId, ?string $bookingDate): array
    {
        // 🛠️ FIX: Add this check to prevent errors with null values
        if (is_null($serviceId) || is_null($bookingDate)) {
            return [];
        }

        $starts = array_keys(self::slotOptionsForService($serviceId, $bookingDate));
        $duration = self::serviceDurationMinutes($serviceId);

        $ends = [];
        foreach ($starts as $h_i) {
            try {
                $end = Carbon::createFromFormat('H:i', $h_i)->addMinutes($duration)->format('H:i');
                $ends[$end] = Carbon::createFromFormat('H:i', $end)->format('g:i A');
            } catch (\Exception $e) {
                // Skip invalid time formats
                continue;
            }
        }

        ksort($ends);
        return $ends;
    }

    /** Default START slot keys 09:00 → 16:00, every 30 minutes. */
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

    /** Service duration in minutes; prefers duration_minutes, falls back to duration, else 60. */
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