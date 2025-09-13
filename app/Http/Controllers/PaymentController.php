<?php

namespace App\http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Stylist;
use App\Payments\PaymentContext;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

            // FIX: Ensure the stylist data in the session is a proper object,
            // which will resolve the "Attempt to read property 'name' on array" error.
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

            // Pass all necessary data to the view, including the bookingDetails variable
            return view('payment.payment', [
                'service' => $service,
                'booking' => $booking,
                'bookingDetails' => $bookingDetails,
                'availablePaymentMethods' => $availablePaymentMethods,
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing payment page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('services.show', $serviceId)
                ->with('error', 'Unable to access payment page. Please try again.');
        }
    }

   /**
 * Process the payment and update the booking record.
 */
public function processPayment(Request $request, $serviceId)
{
    DB::beginTransaction();
    try {
        // Validate payment method
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,credit_card,paypal,bank_transfer,bank_transfer,'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Please select a valid payment method.')
                ->withInput();
        }

        // Get booking details from session
        $bookingDetails = $request->session()->get('booking_details');
        if (!$bookingDetails || !isset($bookingDetails['booking_id'])) {
            DB::rollBack();
            return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                ->with('error', 'Session expired. Please start your booking again.');
        }

        // GET EXISTING BOOKING RECORD
        $booking = Booking::find($bookingDetails['booking_id']);

        if (!$booking) {
            DB::rollBack();
            Log::error('Booking not found', [
                'booking_id' => $bookingDetails['booking_id']
            ]);
            return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                ->with('error', 'Booking record not found. Please start again.');
        }

        // FIX: Get the existing payment record instead of creating a new one
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
        
        // VALIDATE PAYMENT DATA
        $validation = $this->paymentContext->validatePaymentData($paymentData);
        if (!$validation['valid']) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Payment validation failed: ' . implode(', ', $validation['errors']));
        }

        // PROCESS PAYMENT USING STRATEGY PATTERN
        $paymentResult = $this->paymentContext->processPayment($booking->total_price, $paymentData);

        if (!$paymentResult['success']) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', $paymentResult['message'] ?? 'Payment processing failed.');
        }
        
        // FIX: UPDATE THE EXISTING PAYMENT RECORD instead of creating a new one
        $payment->update([
            'payment_method' => $request->payment_method,
            'amount' => $booking->total_price,
            'status' => $paymentResult['payment_status'],
        ]);

        // FIX: Map payment status to valid booking status values
        $bookingStatus = $this->mapPaymentStatusToBookingStatus($paymentResult['payment_status']);
        
        // UPDATE THE BOOKING RECORD WITH THE PAYMENT ID AND STATUS
        // Note: payment_id should already be set, but we'll ensure it's correct
        $booking->payment_id = $payment->id;
        $booking->status = $bookingStatus;
        $booking->save();
        
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

        return redirect()->route('booking.success', $booking->id)
            ->with('success', $paymentResult['message'] ?? 'Your booking has been confirmed!');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payment processing error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()
            ->with('error', 'Unable to process payment. Please try again.')
            ->withInput();
    }
}

    /**
     * Map payment status to valid booking status values
     * This prevents database constraint violations
     */
    private function mapPaymentStatusToBookingStatus(string $paymentStatus): string
    {
        // Map payment statuses to valid booking statuses based on your database schema
        // You'll need to adjust these values based on your actual booking status ENUM values
        $statusMapping = [
            'completed' => 'booked',    // or 'paid', 'active', etc.
            'pending' => 'booked',         // or 'unpaid', 'draft', etc.
            'failed' => 'cancelled',       // or 'failed', 'rejected', etc.
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        // Return mapped status or default to a safe value
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
    $query = Payment::with(['booking.service', 'booking.stylist'])
        ->whereHas('booking', function($q) use ($request) {
            $q->where('customer_email', $request->user()->email);
        });

    // Search by payment reference, booking reference, or service name
    if ($q = trim($request->get('q', ''))) {
        $query->where(function ($qBuilder) use ($q) {
            $qBuilder->where('payment_ref', 'like', "%{$q}%")
                ->orWhere('transaction_id', 'like', "%{$q}%")
                ->orWhereHas('booking', function ($booking) use ($q) {
                    $booking->where('booking_reference', 'like', "%{$q}%")
                        ->orWhereHas('service', function ($service) use ($q) {
                            $service->where('name', 'like', "%{$q}%");
                        });
                });
        });
    }

    // Filter by payment method
    if ($method = $request->get('method')) {
        if (in_array($method, ['cash', 'credit_card', 'paypal', 'bank_transfer'], true)) {
            $query->where('payment_method', $method);
        }
    }

    // Filter by payment status
    if ($status = $request->get('status')) {
        if (in_array($status, ['pending', 'completed', 'failed'], true)) {
            $query->where('status', $status);
        }
    }

    // Date range filter
    if ($from = $request->get('from')) {
        $query->whereDate('created_at', '>=', $from);
    }
    if ($to = $request->get('to')) {
        $query->whereDate('created_at', '<=', $to);
    }

    // Order by newest first
    $query->orderBy('created_at', 'desc');

    $payments = $query->paginate(10)->withQueryString();

    // Calculate summary statistics
    $totalPayments = Payment::whereHas('booking', function($q) use ($request) {
        $q->where('customer_email', $request->user()->email);
    })->sum('amount');

    $completedPayments = Payment::whereHas('booking', function($q) use ($request) {
        $q->where('customer_email', $request->user()->email);
    })->where('status', 'completed')->count();

    $pendingPayments = Payment::whereHas('booking', function($q) use ($request) {
        $q->where('customer_email', $request->user()->email);
    })->where('status', 'pending')->count();

    return view('profile.payment_history', [
        'payments' => $payments,
        'filters' => [
            'q' => $request->get('q', ''),
            'method' => $request->get('method', ''),
            'status' => $request->get('status', ''),
            'from' => $request->get('from', ''),
            'to' => $request->get('to', ''),
        ],
        'stats' => [
            'total_amount' => $totalPayments,
            'completed_count' => $completedPayments,
            'pending_count' => $pendingPayments,
        ]
    ]);
}
}