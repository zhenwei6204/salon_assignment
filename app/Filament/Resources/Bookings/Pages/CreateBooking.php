<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure booking reference is generated if not present
        if (empty($data['booking_reference'])) {
            $data['booking_reference'] = $this->generateBookingReference();
        }

        // Ensure end_time is calculated if not present but booking_time and service_id exist
        if (empty($data['end_time']) && !empty($data['booking_time']) && !empty($data['service_id'])) {
            $data['end_time'] = $this->calculateEndTime($data['booking_time'], $data['service_id']);
        }

        return $data;
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BKG-' . now()->format('Ymd') . '-' . strtoupper(str()->random(5));
        } while (\App\Models\Booking::where('booking_reference', $ref)->exists());

        return $ref;
    }

    private function calculateEndTime(string $bookingTime, int $serviceId): string
    {
        try {
            // Get service duration
            $service = Service::find($serviceId);
            $duration = (int) ($service->duration_minutes ?? $service->duration ?? 60);

            // Parse booking time and add duration
            $startTime = Carbon::createFromFormat('H:i:s', $bookingTime);
            $endTime = $startTime->addMinutes($duration);

            return $endTime->format('H:i:s');
        } catch (\Exception $e) {
            // Fallback to 1 hour later if something goes wrong
            try {
                $startTime = Carbon::createFromFormat('H:i:s', $bookingTime);
                return $startTime->addHour()->format('H:i:s');
            } catch (\Exception $e2) {
                return $bookingTime; // Return original if all fails
            }
        }
    }
}