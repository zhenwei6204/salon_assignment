
@extends('layout.app')

@section('title', 'Booking Confirmed!')

@section('content')
<div class="container">
    <!-- Success Headerrrrrrrrrrrrrrrrrrrrrrrrrrrrrr -->
    <div class="success-header">
        <div class="success-icon">✅</div>
        <h1>Booking Confirmed!</h1>
        <p>Your appointment has been successfully booked</p>
    </div>

    <!-- Booking Details Card -->
    <div class="booking-confirmation-card">
        <div class="confirmation-header">
            <h2>Appointment Details</h2>
            <div class="booking-reference">
                <strong>Booking Reference: {{ $booking->booking_reference }}</strong>
            </div>
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

        <!-- Important Notes -->
        <div class="important-notes">
            <h3>📋 Important Reminders</h3>
            <ul>
                <li>Please arrive 10 minutes early for your appointment</li>
                <li>Bring a valid ID for verification</li>
                <li>Cancellations must be made at least 24 hours in advance</li>
                <li>A confirmation email has been sent to {{ $booking->customer_email }}</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="success-actions">
            <a href="{{ route('categories.index') }}" class="book-another-btn">
                📅 Book Another Service
            </a>
            <a href="{{ url('/') }}" class="home-btn">
                🏠 Back to Home
            </a>
        </div>
    </div>
</div>

<style>
.success-header {
    text-align: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.success-header h1 {
    font-size: 2.5rem;
    margin: 0;
}

.booking-confirmation-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.confirmation-header {
    text-align: center;
    margin-bottom: 2rem;
}

.booking-reference {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 10px;
    margin-top: 1rem;
    font-size: 1.2rem;
    color: #2c5aa0;
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
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.detail-row .label {
    font-weight: 600;
    color: #666;
}

.detail-row .value {
    font-weight: 500;
}

.price {
    color: #4CAF50;
    font-size: 1.2rem;
    font-weight: bold;
}

.important-notes {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.important-notes h3 {
    color: #856404;
    margin-top: 0;
}

.important-notes ul {
    margin: 0;
    padding-left: 1.5rem;
}

.important-notes li {
    margin-bottom: 0.5rem;
    color: #856404;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.book-another-btn, .home-btn {
    padding: 1rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.book-another-btn {
    background: #2c5aa0;
    color: white;
}

.book-another-btn:hover {
    background: #1e3d6f;
}

.home-btn {
    background: #6c757d;
    color: white;
}

.home-btn:hover {
    background: #545b62;
}

@media (max-width: 768px) {
    .success-actions {
        flex-direction: column;
    }
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .detail-row .value {
        margin-top: 0.25rem;
    }
}
</style>
@endsection