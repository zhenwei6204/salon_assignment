<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    /**
     * Display user's refund history
     */
    public function refund(Request $request)
    {
        $query = Refund::with(['payment', 'booking.service', 'booking.stylist'])
            ->forUser($request->user()->email);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('refund_reference', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($booking) use ($search) {
                      $booking->where('booking_reference', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Date range filter
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Calculate statistics
        $userEmail = $request->user()->email;
        $stats = [
            'total_refunds' => Refund::forUser($userEmail)->count(),
            'pending_refunds' => Refund::forUser($userEmail)->where('status', 'pending')->count(),
            'completed_refunds' => Refund::forUser($userEmail)->where('status', 'completed')->count(),
            'total_refunded_amount' => Refund::forUser($userEmail)->where('status', 'completed')->sum('refund_amount')
        ];

        return view('refunds.refund', [
            'refunds' => $refunds,
            'stats' => $stats,
            'filters' => [
                'search' => $request->get('search', ''),
                'status' => $request->get('status', ''),
                'from' => $request->get('from', ''),
                'to' => $request->get('to', ''),
            ]
        ]);
    }

    /**
     * Show refund request form
     */
    public function create(Request $request)
    {
        $bookingId = $request->get('booking_id');
        
        if (!$bookingId) {
            return redirect()->route('bookings.index')
                ->with('error', 'Please select a booking to request refund.');
        }

        $booking = Booking::with(['service', 'stylist', 'payment'])
            ->where('id', $bookingId)
            ->where('customer_email', auth()->user()->email)
            ->first();

        if (!$booking || !$booking->payment) {
            return redirect()->route('bookings.index')
                ->with('error', 'Booking or payment not found.');
        }

        if (!$booking->canBeRefunded()) {
            return redirect()->route('bookings.index')
                ->with('error', 'This booking is not eligible for refund.');
        }

        return view('refunds.create', [
            'booking' => $booking,
            'payment' => $booking->payment
        ]);
    }

    /**
     * Store refund request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'booking_id' => 'required|exists:bookings,id',
            'refund_type' => 'required|in:full,partial',
            'refund_amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $booking = Booking::find($validated['booking_id']);
            
            // Security check
            if ($booking->customer_email !== auth()->user()->email) {
                return redirect()->route('bookings.index')
                    ->with('error', 'Unauthorized refund request.');
            }

            // Validate refund amount
            $maxRefundAmount = $booking->payment->amount - $booking->payment->totalRefundAmount();
            if ($validated['refund_amount'] > $maxRefundAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Refund amount cannot exceed RM{$maxRefundAmount}.");
            }

            // Create refund request
            $refund = Refund::create([
                'payment_id' => $validated['payment_id'],
                'booking_id' => $validated['booking_id'],
                'refund_amount' => $validated['refund_amount'],
                'original_amount' => $booking->payment->amount,
                'refund_type' => $validated['refund_type'],
                'reason' => $validated['reason'],
                'refund_method' => $booking->payment->payment_method,
                'requested_by' => auth()->id(),
                'status' => 'pending'
            ]);

            DB::commit();

            return redirect()->route('refunds.show', $refund)
                ->with('success', 'Refund request submitted successfully! Reference: ' . $refund->refund_reference);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund request failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit refund request. Please try again.');
        }
    }

    /**
     * Show refund details
     */
    public function show(Refund $refund)
    {
        // Security check
        if ($refund->booking->customer_email !== auth()->user()->email) {
            return redirect()->route('refunds.refund')
                ->with('error', 'Unauthorized access.');
        }

        $refund->load(['payment', 'booking.service', 'booking.stylist']);

        return view('refunds.show', compact('refund'));
    }

    /**
     * Cancel refund request
     */
    public function cancel(Refund $refund)
    {
        // Security check
        if ($refund->booking->customer_email !== auth()->user()->email) {
            return redirect()->route('refunds.refund')
                ->with('error', 'Unauthorized access.');
        }

        if (!$refund->canBeCancelled()) {
            return redirect()->route('refunds.show', $refund)
                ->with('error', 'This refund request cannot be cancelled.');
        }

        $refund->update([
            'status' => 'rejected',
            'admin_notes' => 'Cancelled by customer'
        ]);

        return redirect()->route('refunds.refund')
            ->with('success', 'Refund request cancelled successfully.');
    }
}