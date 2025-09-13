@extends('layout.app')

@section('title', 'Booking Confirmed!')

@section('content')
@php
    // Payment details with safe fallbacks
    $payment = $booking->payment ?? null;
    $paymentMethod = $payment?->payment_method ?? 'cash';
    $paymentStatus = $payment?->status ?? 'completed';
    $paymentAmount = $payment?->amount ?? $booking->total_price;
    $paymentDate = $payment?->created_at ?? $booking->created_at;
    
    // Format payment method display
    $paymentMethodDisplay = match($paymentMethod) {
        'cash' => 'Cash Payment at Salon',
        'credit_card' => 'Credit/Debit Card',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank Transfer',
        default => ucfirst(str_replace('_', ' ', $paymentMethod))
    };
    
    // Payment status styling
    $statusClass = match(strtolower($paymentStatus)) {
        'completed' => 'status-completed',
        'pending' => 'status-pending',
        'failed' => 'status-failed',
        default => 'status-default'
    };
    
    $statusIcon = match(strtolower($paymentStatus)) {
        'completed' => '✅',
        'pending' => '⏳',
        'failed' => '❌',
        default => 'ℹ️'
    };
@endphp

<style>
.success-header {
    text-align: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 15px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.success-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(255,255,255,0.05) 10px,
        rgba(255,255,255,0.05) 20px
    );
    animation: shine 3s linear infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.success-header h1 {
    font-size: 2.5rem;
    margin: 0;
    position: relative;
    z-index: 1;
}

.booking-reference {
    background: rgba(255,255,255,0.2);
    padding: 1rem 2rem;
    border-radius: 25px;
    margin-top: 1rem;
    font-size: 1.1rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    display: inline-block;
}

.confirmation-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 1024px) {
    .confirmation-grid {
        grid-template-columns: 1fr;
    }
}

.booking-confirmation-card, .payment-confirmation-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
}

.payment-confirmation-card {
    background: linear-gradient(135deg, #f8f9ff, #ffffff);
    border-left: 4px solid #2c5aa0;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.confirmation-header h2 {
    color: #2c5aa0;
    margin: 0;
    font-size: 1.5rem;
}

.payment-summary {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(44, 90, 160, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(44, 90, 160, 0.1);
}

.payment-status-badge {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1rem;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-completed {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border: 2px solid #b8dabc;
}

.status-pending {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
    border: 2px solid #f0d43a;
}

.status-failed {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border: 2px solid #f1b0b7;
}

.status-default {
    background: linear-gradient(135deg, #e2e3e5, #d6d8db);
    color: #383d41;
    border: 2px solid #c6c8ca;
}

.payment-method-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c5aa0;
}

.payment-icon {
    font-size: 2rem;
}

.detail-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.detail-section:last-child {
    border-bottom: none;
}

.detail-section h3 {
    color: #2c5aa0;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.detail-row, .receipt-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px dotted #ddd;
}

.detail-row:last-child, .receipt-row:last-child {
    border-bottom: none;
}

.detail-row .label, .receipt-row .label {
    font-weight: 600;
    color: #666;
}

.detail-row .value, .receipt-row .value {
    font-weight: 500;
    text-align: right;
}

.price {
    color: #4CAF50;
    font-size: 1.2rem;
    font-weight: bold;
}

.receipt-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.receipt-row.discount .value {
    color: #dc3545;
}

.total-row {
    border-top: 2px solid #2c5aa0 !important;
    border-bottom: none !important;
    padding-top: 1rem;
    margin-top: 0.5rem;
    font-size: 1.1rem;
}

.total-row .value {
    color: #2c5aa0;
    font-size: 1.3rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

.cash-payment-note {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    border: 1px solid #f0d43a;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.note-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.note-content {
    color: #856404;
    font-size: 0.9rem;
    line-height: 1.4;
}

.important-notes {
    background: linear-gradient(135deg, #fff3cd, #ffffff);
    border: 1px solid #ffeaa7;
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
}

.important-notes h3 {
    color: #856404;
    margin-top: 0;
    margin-bottom: 1rem;
}

.important-notes ul {
    margin: 0;
    padding-left: 1.5rem;
}

.important-notes li {
    margin-bottom: 0.75rem;
    color: #856404;
    line-height: 1.5;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.book-another-btn, .home-btn, .print-btn {
    padding: 1rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.book-another-btn {
    background: linear-gradient(135deg, #2c5aa0, #1e3d6f);
    color: white;
    box-shadow: 0 4px 15px rgba(44, 90, 160, 0.3);
}

.book-another-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(44, 90, 160, 0.4);
}

.home-btn {
    background: linear-gradient(135deg, #6c757d, #545b62);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.home-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.print-btn {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
}

.print-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
}

@media (max-width: 768px) {
    .success-actions {
        flex-direction: column;
    }
    
    .detail-row, .receipt-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .detail-row .value, .receipt-row .value {
        text-align: left;
    }
    
    .payment-method-display {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .success-header h1 {
        font-size: 2rem;
    }
    
    .booking-confirmation-card, .payment-confirmation-card {
        padding: 1.5rem;
    }
}

@media print {
    body * {
        visibility: hidden;
    }
    
    .container, .container * {
        visibility: visible;
    }
    
    .success-actions {
        display: none;
    }
    
    .success-header {
        background: #f0f0f0 !important;
        color: #333 !important;
        -webkit-print-color-adjust: exact;
    }
    
    .confirmation-grid {
        grid-template-columns: 1fr;
        page-break-inside: avoid;
    }
}
</style>

<div class="container">
    <!-- Success Header -->
    <div class="success-header">
        <div class="success-icon">✅</div>
        <h1>Booking Confirmed!</h1>
        <p>Your appointment has been successfully booked</p>
        <div class="booking-reference">
            <strong>Booking Reference: {{ $booking->booking_reference }}</strong>
        </div>
    </div>

    <div class="confirmation-grid">
        <!-- Left Column: Booking Details -->
        <div class="booking-confirmation-card">
            <div class="confirmation-header">
                <h2>📅 Appointment Details</h2>
            </div>

            <div class="confirmation-details">
                <!-- Service Information -->
                <div class="detail-section">
                    <h3>💅 Service Details</h3>
                    <div class="detail-row">
                        <span class="label">Service:</span>
                        <span class="value">{{ $booking->service->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Duration:</span>
                        <span class="value">{{ $booking->service->duration }} minutes</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Price:</span>
                        <span class="value price">${{ number_format($booking->total_price, 2) }}</span>
                    </div>
                </div>

                <!-- Appointment Information -->
                <div class="detail-section">
                    <h3>📅 Appointment Information</h3>
                    <div class="detail-row">
                        <span class="label">Stylist:</span>
                        <span class="value">{{ $booking->stylist->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date:</span>
                        <span class="value">{{ date('l, F j, Y', strtotime($booking->booking_date)) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Time:</span>
                        <span class="value">{{ date('g:i A', strtotime($booking->booking_time)) }} - {{ date('g:i A', strtotime($booking->end_time)) }}</span>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="detail-section">
                    <h3>👤 Customer Information</h3>
                    <div class="detail-row">
                        <span class="label">Name:</span>
                        <span class="value">{{ $booking->customer_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value">{{ $booking->customer_email }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Phone:</span>
                        <span class="value">{{ $booking->customer_phone }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Details -->
        <div class="payment-confirmation-card">
            <div class="confirmation-header">
                <h2>💳 Payment Details</h2>
            </div>

            <div class="payment-summary">
                <div class="payment-status-badge {{ $statusClass }}">
                    {{ $statusIcon }} {{ ucfirst($paymentStatus) }}
                </div>
                
                <div class="payment-method-display">
                    <div class="payment-icon">
                        @if($paymentMethod === 'cash')
                            💵
                        @elseif($paymentMethod === 'credit_card')
                            💳
                        @elseif($paymentMethod === 'paypal')
                            🅿️
                        @elseif($paymentMethod === 'bank_transfer')
                            🏦
                        @else
                            💰
                        @endif
                    </div>
                    <div class="payment-method-text">{{ $paymentMethodDisplay }}</div>
                </div>
            </div>

            <div class="payment-details">
                <div class="detail-section">
                    <h3>💰 Transaction Information</h3>
                    <div class="detail-row">
                        <span class="label">Payment Method:</span>
                        <span class="value">{{ $paymentMethodDisplay }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Payment Status:</span>
                        <span class="value">
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusIcon }} {{ ucfirst($paymentStatus) }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Amount Paid:</span>
                        <span class="value price">${{ number_format($paymentAmount, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Payment Date:</span>
                        <span class="value">{{ $paymentDate->format('M j, Y g:i A') }}</span>
                    </div>
                    @if($payment && $payment->id)
                    <div class="detail-row">
                        <span class="label">Payment ID:</span>
                        <span class="value">#{{ $payment->id }}</span>
                    </div>
                    @endif
                </div>

                <!-- Payment Receipt -->
                <div class="detail-section receipt-section">
                    <h3>🧾 Receipt Summary</h3>
                    <div class="receipt-row">
                        <span class="label">Service Charge:</span>
                        <span class="value">${{ number_format($booking->service->price ?? $booking->total_price, 2) }}</span>
                    </div>
                    @if(isset($booking->discount_amount) && $booking->discount_amount > 0)
                    <div class="receipt-row discount">
                        <span class="label">Discount:</span>
                        <span class="value">-${{ number_format($booking->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    @if(isset($booking->tax_amount) && $booking->tax_amount > 0)
                    <div class="receipt-row">
                        <span class="label">Tax:</span>
                        <span class="value">${{ number_format($booking->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="receipt-row total-row">
                        <span class="label"><strong>Total Paid:</strong></span>
                        <span class="value"><strong>${{ number_format($paymentAmount, 2) }}</strong></span>
                    </div>
                </div>

                @if($paymentMethod === 'cash')
                <div class="cash-payment-note">
                    <div class="note-icon">ℹ️</div>
                    <div class="note-content">
                        <strong>Cash Payment Instructions:</strong><br>
                        Please pay at the salon counter upon arrival for your appointment.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Important Notes -->
    <div class="important-notes">
        <h3>📋 Important Reminders</h3>
        <ul>
            <li>Please arrive 10 minutes early for your appointment</li>
            <li>Bring a valid ID for verification</li>
            @if($paymentMethod === 'cash')
            <li><strong>Payment required at salon:</strong> Please bring ${{ number_format($paymentAmount, 2) }} in cash</li>
            @else
            <li>Payment has been processed successfully</li>
            @endif
            <li>A confirmation email has been sent to {{ $booking->customer_email }}</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="success-actions">
        <a href="{{ route('categories.index') }}" class="book-another-btn">
            Book Another Service
        </a>
        <a href="{{ url('/') }}" class="home-btn">
            Back to Home
        </a>
        <button onclick="window.print()" class="print-btn">
            Print Receipt
        </button>
    </div>
</div>

@endsection