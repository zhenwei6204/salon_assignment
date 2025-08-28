<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Stylist;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Step 1: Select a stylist for the service
     */
    public function selectStylist(Service $service)
    {
        try {
            // Get stylists who can perform this service
            $stylists = $service->stylists()
                              ->where('is_active', true)
                              ->get();
            
            // Convert to array format to match your template expectations
            $stylistsArray = [];
            foreach ($stylists as $stylist) {
                $stylistsArray[] = [
                    'id' => $stylist->id,
                    'name' => $stylist->name,
                    'title' => $stylist->title ?? 'Professional Stylist',
                    'experience_years' => $stylist->experience_years ?? '5',
                    'specializations' => $stylist->specializations ?? 'Hair Styling, Color',
                    'rating' => $stylist->rating ?? 4.8,
                    'review_count' => $stylist->review_count ?? '150'
                ];
            }
                              
            return view('booking.category.stylists', [
                'service' => $service,
                'stylists' => $stylistsArray
            ]);
        } catch (\Exception $e) {
            Log::error('Error in selectStylist: ' . $e->getMessage(), [
                'service_id' => $service->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('services.show', $service)
                           ->with('error', 'Unable to load stylists. Please try again.');
        }
    }
    
    /**
     * Step 2: Select date and time
     */
    public function selectTime(Request $request, Service $service, Stylist $stylist)
    {
        try {
            $selectedDate = $request->get('date', now()->format('Y-m-d'));
            
            // Validate date is not in the past
            if (Carbon::parse($selectedDate)->isPast() && !Carbon::parse($selectedDate)->isToday()) {
                $selectedDate = now()->format('Y-m-d');
            }
            
            $availableSlots = $this->getAvailableTimeSlots($service, $stylist, $selectedDate);
            
            return view('booking.category.times', [
                'service' => $service,
                'stylist' => $stylist,
                'selectedDate' => $selectedDate,
                'availableSlots' => $availableSlots
            ]);
        } catch (\Exception $e) {
            Log::error('Error in selectTime: ' . $e->getMessage(), [
                'service_id' => $service->id,
                'stylist_id' => $stylist->id,
                'selected_date' => $selectedDate ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('booking.select.stylist', $service)
                           ->with('error', 'Unable to load available times. Please try again.');
        }
    }
    
    /**
     * Step 3: Show booking confirmation
     */
    public function confirmation(Request $request, Service $service, Stylist $stylist)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i:s'
            ]);

            if ($validator->fails()) {
                return redirect()->route('booking.select.time', [$service, $stylist])
                               ->withErrors($validator)
                               ->with('error', 'Please select a valid date and time.');
            }
            
            $selectedDate = $request->get('date');
            $selectedTime = $request->get('time');
            
            // Calculate end time based on service duration
            $startDateTime = Carbon::parse($selectedDate . ' ' . $selectedTime);
            $endDateTime = $startDateTime->copy()->addMinutes($service->duration);
            
            // Check if slot is still available
            $isAvailable = $this->checkSlotAvailability($stylist, $selectedDate, $selectedTime, $endDateTime->format('H:i:s'));
            
            if (!$isAvailable) {
                return redirect()->route('booking.select.time', [$service, $stylist])
                               ->with('error', 'This time slot is no longer available. Please select another time.');
            }
            
            return view('booking.category.confirmation', [
                'service' => $service,
                'stylist' => $stylist,
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'endDateTime' => $endDateTime
            ]);
        } catch (\Exception $e) {
            Log::error('Error in confirmation: ' . $e->getMessage(), [
                'service_id' => $service->id,
                'stylist_id' => $stylist->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('booking.select.time', [$service, $stylist])
                           ->with('error', 'Unable to process confirmation. Please try again.');
        }
    }
    
    /**
     * Step 4: Store the booking
     */
    public function store(Request $request)
    {
        try {
            // Enhanced validation with custom messages
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|exists:services,id',
                'stylist_id' => 'required|exists:stylists,id',
                'booking_date' => 'required|date|after_or_equal:today',
                'booking_time' => 'required|date_format:H:i:s',
                'customer_name' => 'required|string|max:255|min:2',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20|min:10',
                'special_requests' => 'nullable|string|max:1000'
            ], [
                'service_id.exists' => 'The selected service is not available.',
                'stylist_id.exists' => 'The selected stylist is not available.',
                'booking_date.after_or_equal' => 'Booking date must be today or in the future.',
                'booking_time.date_format' => 'Please select a valid time slot.',
                'customer_name.min' => 'Name must be at least 2 characters.',
                'customer_phone.min' => 'Phone number must be at least 10 digits.',
                'special_requests.max' => 'Special requests cannot exceed 1000 characters.'
            ]);

            if ($validator->fails()) {
                Log::warning('Booking validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['_token'])
                ]);
                
                return redirect()->back()
                               ->withErrors($validator)
                               ->withInput()
                               ->with('error', 'Please correct the errors below.');
            }
            
            // Load models with error checking
            $service = Service::find($request->service_id);
            $stylist = Stylist::find($request->stylist_id);
            
            if (!$service || !$stylist) {
                Log::error('Service or Stylist not found', [
                    'service_id' => $request->service_id,
                    'stylist_id' => $request->stylist_id
                ]);
                
                return redirect()->route('categories.index')
                               ->with('error', 'The selected service or stylist is no longer available.');
            }
            
            // Check if service duration is valid
            if (!$service->duration || $service->duration <= 0) {
                Log::error('Invalid service duration', [
                    'service_id' => $service->id,
                    'duration' => $service->duration
                ]);
                
                return redirect()->back()
                               ->with('error', 'Service configuration error. Please contact support.')
                               ->withInput();
            }
            
            // Calculate end time
            $startDateTime = Carbon::parse($request->booking_date . ' ' . $request->booking_time);
            $endDateTime = $startDateTime->copy()->addMinutes($service->duration);
            
            // Validate booking is within business hours
            if (!$this->isWithinBusinessHours($startDateTime, $endDateTime)) {
                return redirect()->back()
                               ->with('error', 'Selected time is outside business hours.')
                               ->withInput();
            }
            
            // Double-check availability before booking
            $isAvailable = $this->checkSlotAvailability(
                $stylist, 
                $request->booking_date, 
                $request->booking_time, 
                $endDateTime->format('H:i:s')
            );
            
            if (!$isAvailable) {
                Log::warning('Time slot no longer available during booking', [
                    'stylist_id' => $stylist->id,
                    'booking_date' => $request->booking_date,
                    'booking_time' => $request->booking_time
                ]);
                
                return redirect()->route('booking.select.time', [$service, $stylist])
                               ->with('error', 'This time slot is no longer available. Please select another time.');
            }
            
            // Generate booking reference
            $bookingReference = $this->generateBookingReference();
            if (!$bookingReference) {
                throw new \Exception('Failed to generate booking reference');
            }
            
            // Use database transaction to ensure data consistency
            DB::beginTransaction();
            
            $bookingData = [
                'service_id' => $request->service_id,
                'stylist_id' => $request->stylist_id,
                'customer_name' => trim($request->customer_name),
                'customer_email' => strtolower(trim($request->customer_email)),
                'customer_phone' => trim($request->customer_phone),
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'end_time' => $endDateTime->format('H:i:s'),
                'total_price' => $service->price,
                'status' => 'confirmed',
                'booking_reference' => $bookingReference,
                'special_requests' => $request->special_requests ? trim($request->special_requests) : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            Log::info('Creating booking with data', $bookingData);
            
            $booking = Booking::create($bookingData);
            
            if (!$booking) {
                throw new \Exception('Failed to create booking record');
            }
            
            DB::commit();
            
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference
            ]);
            
            // TODO: Send confirmation email to customer
            // try {
            //     Mail::to($booking->customer_email)->send(new BookingConfirmation($booking));
            // } catch (\Exception $emailError) {
            //     Log::warning('Failed to send confirmation email', [
            //         'booking_id' => $booking->id,
            //         'error' => $emailError->getMessage()
            //     ]);
            // }
            
            return redirect()->route('booking.success', $booking)
                           ->with('success', 'Your booking has been confirmed!');
                           
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            
            Log::error('Database error during booking creation', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'request_data' => $request->except(['_token'])
            ]);
            
            // Check for common database issues
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return redirect()->back()
                               ->with('error', 'A booking with these details already exists.')
                               ->withInput();
            }
            
            return redirect()->back()
                           ->with('error', 'Database error occurred. Please try again or contact support.')
                           ->withInput();
                           
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Unexpected error during booking creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token'])
            ]);
            
            return redirect()->back()
                           ->with('error', 'An unexpected error occurred. Please try again or contact support if the problem persists.')
                           ->withInput();
        }
    }
    
    /**
     * Show booking success page
     */
    public function success(Booking $booking)
    {
        try {
            // Load relationships
            $booking->load(['service', 'stylist']);
            
            return view('booking.category.success', compact('booking'));
        } catch (\Exception $e) {
            Log::error('Error loading success page', [
                'booking_id' => $booking->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('categories.index')
                           ->with('error', 'Unable to display booking confirmation. Please contact support with your booking reference.');
        }
    }
    
    /**
     * Check if booking time is within business hours
     */
    private function isWithinBusinessHours(Carbon $startTime, Carbon $endTime)
    {
        $businessStart = 9; // 9 AM
        $businessEnd = 18;  // 6 PM
        
        return $startTime->hour >= $businessStart && $endTime->hour <= $businessEnd;
    }
    
    /**
     * Get available time slots for a specific date
     */
    private function getAvailableTimeSlots(Service $service, Stylist $stylist, $date)
    {
        try {
            // Business hours configuration
            $businessHours = [
                'start' => 9,  // 9 AM
                'end' => 18,   // 6 PM
            ];
            
            $slotDuration = 30; // 30 minutes slots
            $serviceDuration = $service->duration; // in minutes
            
            if (!$serviceDuration || $serviceDuration <= 0) {
                Log::error('Invalid service duration for time slots', [
                    'service_id' => $service->id,
                    'duration' => $serviceDuration
                ]);
                return [];
            }
            
            $slots = [];
            $currentDate = Carbon::parse($date);
            $now = Carbon::now();
            
            // Start from business hours or current time if today
            if ($currentDate->isToday()) {
                $startHour = max($businessHours['start'], $now->hour + 1);
            } else {
                $startHour = $businessHours['start'];
            }
            
            $currentTime = $currentDate->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
            $endTime = $currentDate->copy()->setHour($businessHours['end'])->setMinute(0)->setSecond(0);
            
            // Subtract service duration to ensure service can be completed within business hours
            $lastPossibleSlot = $endTime->copy()->subMinutes($serviceDuration);
            
            while ($currentTime <= $lastPossibleSlot) {
                $slotEndTime = $currentTime->copy()->addMinutes($serviceDuration);
                
                // Check if slot is available (not booked)
                $isBooked = Booking::where('stylist_id', $stylist->id)
                                 ->where('booking_date', $date)
                                 ->where('status', '!=', 'cancelled')
                                 ->where(function($query) use ($currentTime, $slotEndTime) {
                                     $query->where(function($q) use ($currentTime, $slotEndTime) {
                                         // Check if any existing booking overlaps with this slot
                                         $q->whereRaw("CONCAT(booking_date, ' ', booking_time) < ?", [$slotEndTime->format('Y-m-d H:i:s')])
                                           ->whereRaw("CONCAT(booking_date, ' ', end_time) > ?", [$currentTime->format('Y-m-d H:i:s')]);
                                     });
                                 })
                                 ->exists();
                
                if (!$isBooked) {
                    $slots[] = $currentTime->format('H:i:s');
                }
                
                $currentTime->addMinutes($slotDuration);
            }
            
            return $slots;
        } catch (\Exception $e) {
            Log::error('Error generating time slots', [
                'service_id' => $service->id,
                'stylist_id' => $stylist->id,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Check if a specific time slot is available
     */
    private function checkSlotAvailability(Stylist $stylist, $date, $startTime, $endTime)
    {
        try {
            return !Booking::where('stylist_id', $stylist->id)
                          ->where('booking_date', $date)
                          ->where('status', '!=', 'cancelled')
                          ->where(function($query) use ($startTime, $endTime) {
                              $query->where(function($q) use ($startTime, $endTime) {
                                  // Check for any overlap
                                  $q->where('booking_time', '<', $endTime)
                                    ->where('end_time', '>', $startTime);
                              });
                          })
                          ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking slot availability', [
                'stylist_id' => $stylist->id,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'error' => $e->getMessage()
            ]);
            return false; // Assume not available if there's an error
        }
    }
    
    /**
     * Generate unique booking reference
     */
    private function generateBookingReference()
    {
        try {
            $maxAttempts = 10;
            $attempts = 0;
            
            do {
                $reference = 'BK' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                $attempts++;
                
                if ($attempts >= $maxAttempts) {
                    Log::error('Failed to generate unique booking reference after max attempts');
                    return null;
                }
            } while (Booking::where('booking_reference', $reference)->exists());
            
            return $reference;
        } catch (\Exception $e) {
            Log::error('Error generating booking reference', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}