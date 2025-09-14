<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Stylist;
use App\Payments\PaymentContext;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Cache; 


class PaymentController extends Controller
{
    private PaymentContext $paymentContext;

    public function __construct(PaymentContext $paymentContext)
    {
        $this->paymentContext = $paymentContext;
    }

    /**
     * Show payment page with available payment methods
     */
    public function makePayment($serviceId)
    {
          $banks = [
            'Maybank',
            'CIMB Bank',
            'Public Bank',
            'RHB Bank',
            'Hong Leong Bank',
            'AmBank',
            'Bank Islam',
            'Bank Rakyat',
        ];
        try {
            Log::debug('Making payment for service:', ['service_id' => $serviceId]);

            $service = Service::findOrFail($serviceId);
            $bookingDetails = session()->get('booking_details', []);
            
            if (empty($bookingDetails) || !isset($bookingDetails['booking_id'])) {
                Log::error('Booking details or booking_id is missing in session.');
                return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                    ->with('error', 'Session expired or missing booking details. Please start again.');
            }

            // Verify the booking exists and load its relationships
            $booking = Booking::with(['stylist', 'service'])->find($bookingDetails['booking_id']);
            if (!$booking) {
                Log::error('Booking not found:', ['booking_id' => $bookingDetails['booking_id']]);
                return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                    ->with('error', 'Booking not found. Please start again.');
            }

            // FIX: Ensure the stylist data in the session is a proper object
            if ($booking->stylist) {
                $bookingDetails['stylist'] = $booking->stylist;
                session()->put('booking_details', $bookingDetails);
            }

            // Get all available payment methods from the PaymentContext
            $availablePaymentMethods = $this->paymentContext->getAvailablePaymentMethods();

            Log::debug('Payment page data:', [
                'booking_details_keys' => array_keys($bookingDetails),
                'has_stylist' => isset($bookingDetails['stylist']),
                'booking_id' => $booking->id,
                'stylist_name' => $booking->stylist->name ?? 'N/A'
            ]);

            // Pass all necessary data to the view
            return view('payment.payment', [
                'service' => $service,
                'booking' => $booking,
                'bookingDetails' => $bookingDetails,
                'availablePaymentMethods' => $availablePaymentMethods,
                'banks' => $banks,
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing payment page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('services.show', $serviceId)
                ->with('error', 'Unable to access payment page. Please try again.');
        }
    }


public function processPayment(Request $request, $serviceId)
{
    $banks = [
        'Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 
        'Hong Leong Bank', 'AmBank', 'Bank Islam', 'Bank Rakyat',
    ];
    
    DB::beginTransaction();
    try {
        Log::debug('Processing payment request - RAW DATA:', [
            'service_id' => $serviceId,
            'payment_method' => $request->payment_method,
            'all_request_data' => $request->except(['card_number', 'cvv']) // Don't log sensitive data
        ]);

        // STEP 1: Basic validation first
        $basicValidator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,credit_card,paypal,bank_transfer',
        ]);

        if ($basicValidator->fails()) {
            DB::rollBack();
            Log::warning('Basic validation failed:', ['errors' => $basicValidator->errors()]);
            return redirect()->back()
                ->withErrors($basicValidator)
                ->with('error', 'Invalid payment method selected.')
                ->withInput();
        }

        // STEP 2: Get booking details from session
        $bookingDetails = $request->session()->get('booking_details');
        if (!$bookingDetails || !isset($bookingDetails['booking_id'])) {
            DB::rollBack();
            Log::error('Session booking details missing during payment processing', [
                'session_data' => $request->session()->all()
            ]);
            return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                ->with('error', 'Session expired. Please start your booking again.');
        }

        // STEP 3: Conditional validation based on payment method
        $validationRules = [];
        
        switch ($request->payment_method) {
            case 'credit_card':
                $validationRules = [
                    'cardholder_name' => 'required|string|max:100',
                    'card_number' => 'required|string|min:13|max:19',
                    'expiry_date' => 'required|string|size:5|regex:/^[0-9]{2}\/[0-9]{2}$/',
                    'cvv' => 'required|string|min:3|max:4|regex:/^[0-9]{3,4}$/',
                ];
                break;
                
            case 'paypal':
                $validationRules = [
                    'paypal_email' => 'required|email|max:255',
                ];
                break;
                
            case 'bank_transfer':
                $validationRules = [
                    'account_holder_name' => 'required|string|max:100',
                    'bank_name' => implode(',', $banks),
                    'account_number' => 'required|string|min:7|max:14',
                    'routing_number' => 'nullable|string|max:12',
                ];
                break;
                
            case 'cash':
                // No additional validation needed for cash
                break;
        }

        if (!empty($validationRules)) {
            $validator = Validator::make($request->all(), $validationRules);
            
            if ($validator->fails()) {
                DB::rollBack();
                Log::warning('Payment-specific validation failed:', [
                    'payment_method' => $request->payment_method,
                    'errors' => $validator->errors()
                ]);
                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', 'Please correct the validation errors.')
                    ->withInput();
            }
        }

        // GET EXISTING BOOKING RECORD
        $booking = Booking::find($bookingDetails['booking_id']);

        if (!$booking) {
            DB::rollBack();
            Log::error('Booking not found during payment processing', [
                'booking_id' => $bookingDetails['booking_id']
            ]);
            return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                ->with('error', 'Booking record not found. Please start again.');
        }

        // Get the existing payment record
        $payment = Payment::where('booking_id', $booking->id)
                         ->where('status', 'pending')
                         ->first();

        if (!$payment) {
            DB::rollBack();
            Log::error('Pending payment not found for booking', [
                'booking_id' => $booking->id
            ]);
            return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                ->with('error', 'Payment record not found. Please start again.');
        }
        
        // Use the PaymentContext to set the strategy
        $this->paymentContext->setStrategyByMethod($request->payment_method);

        // Prepare payment data using the existing helper method
        $paymentData = $this->preparePaymentData($request, $bookingDetails);
        
        Log::debug('Payment data prepared:', [
            'payment_method' => $request->payment_method,
            'booking_id' => $booking->id,
            'payment_id' => $payment->id
        ]);
        
        // VALIDATE PAYMENT DATA
        $validation = $this->paymentContext->validatePaymentData($paymentData);
        if (!$validation['valid']) {
            DB::rollBack();
            Log::warning('Payment data validation failed:', ['errors' => $validation['errors']]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Payment validation failed: ' . implode(', ', $validation['errors']));
        }

        // PROCESS PAYMENT USING STRATEGY PATTERN
        $paymentResult = $this->paymentContext->processPayment($booking->total_price, $paymentData);

        Log::debug('Payment processing result:', [
            'success' => $paymentResult['success'],
            'payment_status' => $paymentResult['payment_status'] ?? 'unknown',
            'message' => $paymentResult['message'] ?? 'No message'
        ]);

        if (!$paymentResult['success']) {
            DB::rollBack();
            Log::error('Payment processing failed:', ['result' => $paymentResult]);
            return redirect()->back()
                ->withInput()
                ->with('error', $paymentResult['message'] ?? 'Payment processing failed.');
        }
        
        // UPDATE THE EXISTING PAYMENT RECORD
        $payment->update([
            'payment_method' => $request->payment_method,
            'amount' => $booking->total_price,
            'status' => $paymentResult['payment_status'],
        ]);

        Log::debug('Payment record updated:', [
            'payment_id' => $payment->id,
            'payment_method' => $payment->payment_method,
            'status' => $payment->status,
            'amount' => $payment->amount
        ]);

        // Map payment status to valid booking status values
        $bookingStatus = $this->mapPaymentStatusToBookingStatus($paymentResult['payment_status']);
        
        // UPDATE THE BOOKING RECORD WITH THE PAYMENT ID AND STATUS
        $booking->payment_id = $payment->id;
        $booking->status = $bookingStatus;
        $booking->save();
        
        Log::debug('Booking record updated:', [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'payment_id' => $booking->payment_id
        ]);
        
        DB::commit();

        // Clear session data
        $request->session()->forget('booking_details');

        Log::info('Payment processed and records updated successfully', [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentResult['payment_status'],
            'booking_status' => $bookingStatus
        ]);

        return redirect()->route('booking.success', ['id' => $booking->id])
                        ->with('success', 'Payment successful and booking confirmed!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payment processing error: ' . $e->getMessage(), [
            'service_id' => $serviceId,
            'payment_method' => $request->payment_method ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()
            ->with('error', 'Unable to process payment. Please try again.')
            ->withInput();
    }
}

    /**
     * Map payment status to valid booking status values
     */
    private function mapPaymentStatusToBookingStatus(string $paymentStatus): string
    {
        $statusMapping = [
            'completed' => 'booked',
            'pending' => 'booked',
            'failed' => 'cancelled',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        return $statusMapping[$paymentStatus] ?? 'booked';
    }

    private function preparePaymentData(Request $request, array $bookingDetails): array
    {
        $basePaymentData = [
            'customer_name' => $bookingDetails['customer_name'],
            'customer_email' => $bookingDetails['customer_email'],
            'customer_phone' => $bookingDetails['customer_phone'] ?? '',
            'booking_reference' => $bookingDetails['booking_reference'] ?? session()->getId() . '_' . time()
        ];

        switch ($request->payment_method) {
            case 'credit_card':
                return array_merge($basePaymentData, [
                    'card_number' => $request->input('card_number'),
                    'expiry_date' => $request->input('expiry_date'),
                    'cvv' => $request->input('cvv'),
                    'cardholder_name' => $request->input('cardholder_name', $bookingDetails['customer_name'])
                ]);

            case 'paypal':
                return array_merge($basePaymentData, [
                    'paypal_email' => $request->input('paypal_email', $bookingDetails['customer_email'])
                ]);

            case 'bank_transfer':
                return array_merge($basePaymentData, [
                    'account_holder_name' => $request->input('account_holder_name', $bookingDetails['customer_name']),
                    'bank_name' => $request->input('bank_name'),
                    'account_number' => $request->input('account_number'),
                    'routing_number' => $request->input('routing_number')
                ]);

            case 'cash':
            default:
                return $basePaymentData;
        }
    }

  public function paymentHistory(Request $request)
{
    try {
        $user = $request->user();
        
        // STEP 1: Get user details from teammate's User Service
        $userDetails = $this->getUserDetailsFromTeammateService($user->id);
        
        // Add fallback if user service is down
        if (!$userDetails) {
            $userDetails = $this->getFallbackUserDetails($user->id);
        }
        
        // STEP 2: Get payment data from YOUR OWN internal service
       
        $query = Payment::with(['booking.service', 'booking.stylist'])
            ->whereHas('booking', function($q) use ($user) {
                $q->where('customer_email', $user->email);
            });

        // Apply filters using internal query
        $this->applyPaymentFilters($query, $request);

        $payments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Calculate statistics using internal service
        $stats = $this->calculateInternalPaymentStats($user);

        Log::info('Successfully loaded payment history', [
            'user_id' => $user->id,
            'total_payments' => $payments->total(),
            'user_service_status' => $userDetails ? 'success' : 'fallback'
        ]);

        return view('profile.payment_history', [
            'payments' => $payments,
            'filters' => [
                'q' => $request->get('q', ''),
                'method' => $request->get('method', ''),
                'status' => $request->get('status', ''),
                'from' => $request->get('from', ''),
                'to' => $request->get('to', ''),
            ],
            'stats' => $stats,
            'user_details' => $userDetails, // From teammate's service
            'data_source' => 'Internal Payments + External User Service'
        ]);

    } catch (\Exception $e) {
        Log::error('Payment history error: ' . $e->getMessage(), [
            'user_id' => $request->user()->id ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);

        return view('profile.payment_history', [
            'payments' => collect([]),
            'filters' => [
                'q' => $request->get('q', ''),
                'method' => $request->get('method', ''),
                'status' => $request->get('status', ''),
                'from' => $request->get('from', ''),
                'to' => $request->get('to', ''),
            ],
            'stats' => [
                'total_amount' => 0,
                'completed_count' => 0,
                'pending_count' => 0,
                'failed_count' => 0,
            ],
            'user_details' => $this->getFallbackUserDetails($request->user()->id ?? 0),
            'error' => 'Unable to load payment history: ' . $e->getMessage(),
            'data_source' => 'Fallback Service'
        ]);
    }
}

/**
 * Get user details from teammate's User Service (separate from payment data)
 */
private function getUserDetailsFromTeammateService($userId)
{
    try {
        $baseUrl = config('services.user_module.base_url');
        $timeout = config('services.user_module.timeout');
        
        $response = Http::timeout($timeout)
            ->get("{$baseUrl}/api/users/{$userId}");
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        
        
        return null;
        
    } catch (\Exception $e) {
        \Log::error("Error fetching user details: " . $e->getMessage());
        return null;
    }
}
    private function applyPaymentFilters($query, Request $request)
    {
        // Search filter
        if ($search = $request->get('q')) {
            $query->where(function($q) use ($search) {
                $q->where('payment_reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('booking', function($subQ) use ($search) {
                      $subQ->where('booking_reference', 'LIKE', "%{$search}%")
                           ->orWhereHas('service', function($serviceQ) use ($search) {
                               $serviceQ->where('name', 'LIKE', "%{$search}%");
                           });
                  });
            });
        }

        // Payment method filter
        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Date range filters
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
    }

    /**
     * Calculate payment statistics using internal service
     */
    private function calculateInternalPaymentStats($user)
    {
        $baseQuery = Payment::whereHas('booking', function($q) use ($user) {
            $q->where('customer_email', $user->email);
        });

        return [
            'total_amount' => $baseQuery->where('status', 'completed')->sum('amount'),
            'completed_count' => $baseQuery->where('status', 'completed')->count(),
            'pending_count' => $baseQuery->where('status', 'pending')->count(),
            'failed_count' => $baseQuery->where('status', 'failed')->count(),
        ];
    }

    
    /**
     * Get fallback user details when teammate's service fails
     */
    private function getFallbackUserDetails($userId)
    {
        if (config('services.user_module.enable_fallback', true)) {
            return [
               'id' => null, 
                'name' => config('services.user_module.fallback_user_name', 'Unknown User'),
                'email' => 'user@example.com',
                'roles' => 'None',
                'source' => 'fallback'
            ];
              
             
               
            
        }
        
        return null;
    }

}