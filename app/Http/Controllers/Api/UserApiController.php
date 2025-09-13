<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    // Get user payment history (provider API)
    public function paymentHistory(Request $request)
    {
        $user = $request->user();

        $query = Payment::with(['booking.service', 'booking.stylist'])
            ->whereHas('booking', function($q) use ($user) {
                $q->where('customer_email', $user->email);
            });

        // Filters (optional: q, method, status, date)
        if ($search = trim($request->get('q', ''))) {
            $query->where(function ($qBuilder) use ($search) {
                $qBuilder->where('payment_ref', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($booking) use ($search) {
                        $booking->where('booking_reference', 'like', "%{$search}%")
                            ->orWhereHas('service', function ($service) use ($search) {
                                $service->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stats
        $totalPayments = $payments->sum('amount');
        $completedPayments = $payments->where('status', 'completed')->count();
        $pendingPayments = $payments->where('status', 'pending')->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'payments' => $payments,
            'stats' => [
                'total_amount' => $totalPayments,
                'completed_count' => $completedPayments,
                'pending_count' => $pendingPayments,
            ]
        ]);
    }
}
