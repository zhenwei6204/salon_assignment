<?php

namespace App\Http\Controllers;

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

            // Verify the booking exists
            $booking = Booking::find($bookingDetails['booking_id']);
            if (!$booking) {
                Log::error('Booking not found:', ['booking_id' => $bookingDetails['booking_id']]);
                return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                    ->with('error', 'Booking not found. Please start again.');
            }

            // Get all available payment methods
            $availablePaymentMethods = $this->paymentContext->getAvailablePaymentMethods();

            return view('payment.payment', [
                'service' => $service,
                'booking' => $booking,
                'bookingDetails' => $bookingDetails,
                'availablePaymentMethods' => $availablePaymentMethods
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing payment page: ' . $e->getMessage());
            return redirect()->route('services.show', $serviceId)
                ->with('error', 'Unable to access payment page. Please try again.');
        }
    }

    /**
     * Process payment using Strategy Pattern - ONLY UPDATES EXISTING RECORDS
     */
    public function processPayment(Request $request, $serviceId)
    {
        DB::beginTransaction();

        try {
            // Validate payment method
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:cash,credit_card,paypal,bank_transfer'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', 'Please select a valid payment method.')
                    ->withInput();
            }

            // Get booking details from session
            $bookingDetails = $request->session()->get('booking_details');
            if (!$bookingDetails || !isset($bookingDetails['booking_id'], $bookingDetails['payment_id'])) {
                DB::rollBack();
                return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                    ->with('error', 'Session expired. Please start your booking again.');
            }

            // GET EXISTING BOOKING AND PAYMENT RECORDS
            $booking = Booking::find($bookingDetails['booking_id']);
            $payment = Payment::find($bookingDetails['payment_id']);

            if (!$booking || !$payment) {
                DB::rollBack();
                Log::error('Booking or payment not found', [
                    'booking_id' => $bookingDetails['booking_id'],
                    'payment_id' => $bookingDetails['payment_id']
                ]);
                return redirect()->route('booking.select.stylist', ['service' => $serviceId])
                    ->with('error', 'Booking or payment record not found. Please start again.');
            }

            // SET PAYMENT STRATEGY
            $this->paymentContext->setStrategyByMethod($request->payment_method);

            // PREPARE PAYMENT DATA
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
            $paymentResult = $this->paymentContext->processPayment($payment->amount, $paymentData);

            if (!$paymentResult['success']) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', $paymentResult['message'] ?? 'Payment processing failed.');
            }

            // UPDATE EXISTING PAYMENT RECORD
            $payment->update([
                'payment_method' => $request->payment_method,
                'status' => $paymentResult['payment_status']
            ]);

            // UPDATE EXISTING BOOKING STATUS
            $booking->update([
                'status' => 'confirmed'
            ]);

            DB::commit();

            // Clear session data
            $request->session()->forget('booking_details');

            // Store payment result in session for success page
            $request->session()->flash('payment_result', $paymentResult);

            Log::info('Payment processed and records updated successfully', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentResult['payment_status']
            ]);

            // Redirect to success page
            return redirect()->route('booking.success', ['booking' => $booking])
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
     * Prepare payment data based on payment method
     */
    private function preparePaymentData(Request $request, array $bookingDetails): array
    {
        $basePaymentData = [
            'customer_name' => $bookingDetails['customer_name'],
            'customer_email' => $bookingDetails['customer_email'],
            'customer_phone' => $bookingDetails['customer_phone'],
            'booking_reference' => session()->getId() . '_' . time()
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
}