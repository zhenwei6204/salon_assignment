@extends('layout.app')

@section('title', 'Confirm Booking - ' . $service->name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>✅ Confirm Your Booking</h1>
        <p>Review your appointment details before confirming</p>
    </div>

    <!-- Display error messages -->
    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    <!-- Booking Progress -->
    <div class="booking-progress">
        <div class="progress-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Select Stylist</span>
        </div>
        <div class="progress-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Choose Date & Time</span>
        </div>
        <div class="progress-step active">
            <span class="step-number">3</span>
            <span class="step-label">Confirm Booking</span>
        </div>
    </div>

    <!-- Booking Summary -->
    <div class="booking-summary">
        <h2 class="section-title">Appointment Summary</h2>

        <div class="summary-card">
            <div class="service-details-summary">
                <h3>💅 Service Details</h3>
                <div class="detail-row">
                    <span class="label">Service:</span>
                    <span class="value">{{ $service->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Category:</span>
                    <span class="value">{{ $service->category_name ?? 'Beauty Service' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Duration:</span>
                    <span class="value">{{ $service->duration }} minutes</span>
                </div>
                <div class="detail-row">
                    <span class="label">Price:</span>
                    <span class="value price-highlight">${{ number_format($service->price, 2) }}</span>
                </div>
            </div>

            <div class="appointment-details-summary">
                <h3>📅 Appointment Details</h3>
                <div class="detail-row">
                    <span class="label">Stylist:</span>
                    <span class="value">{{ $stylist->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ date('l, F j, Y', strtotime($selectedDate)) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Time:</span>
                    <span class="value">{{ date('g:i A', strtotime($selectedTime)) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">End Time:</span>
                    <span class="value">{{ date('g:i A', strtotime($selectedTime . ' +' . $service->duration . ' minutes')) }}</span>
                </div>
            </div>
        </div>

        <!-- Service Description -->
        <div class="service-description-summary">
            <h3>ℹ️ What to Expect</h3>
            <p>{{ $service->description ?? 'Professional service tailored to your needs.' }}</p>

            @if(!empty($service->benefits))
            <h4>Benefits:</h4>
            <p>{{ $service->benefits }}</p>
            @endif
        </div>

        <!-- Customer Information Form -->
        <div class="customer-info-section">
            <h3>📋 Your Information</h3>
            <form method="POST" action="{{ route('booking.store') }}" class="customer-form">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                <input type="hidden" name="stylist_id" value="{{ $stylist->id }}">
                <input type="hidden" name="booking_date" value="{{ $selectedDate }}">
                <input type="hidden" name="booking_time" value="{{ $selectedTime }}">

                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required 
                               value="{{ old('customer_name') }}">
                        @error('customer_name')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required
                               value="{{ old('customer_email') }}">
                        @error('customer_email')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required
                               value="{{ old('customer_phone') }}">
                        @error('customer_phone')
                        <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea id="special_requests" name="special_requests" rows="3" 
                              placeholder="Any special requests or notes...">{{ old('special_requests') }}</textarea>
                </div>

                <!-- Important Notes -->
                <div class="booking-notes">
                    <h4>📋 Important Notes</h4>
                    <ul>
                        <li>Please arrive 10 minutes early for your appointment</li>
                        <li>Cancellations must be made at least 24 hours in advance</li>
                        <li>Late arrivals may result in shortened service time</li>
                        <li>Payment is due at the time of service</li>
                    </ul>
                </div>

                <!-- Confirmation Actions -->
                <div class="confirmation-actions">
                    <div class="action-buttons">
                        <button type="submit" class="confirm-booking-btn">
                            ✅ Confirm Booking
                        </button>

                        <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" 
                           class="modify-booking-btn">
                            🔄 Modify Time
                        </a>
                    </div>

                    <p class="confirmation-note">
                        By confirming this booking, you agree to our terms and conditions.
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="navigation-links">
        <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" class="back-link">
            ← Back to Time Selection
        </a>
    </div>
</div>

<script>
    // Add confirmation dialog
    document.querySelector('.confirm-booking-btn').addEventListener('click', function (e) {
        if (!confirm('Are you sure you want to confirm this booking?')) {
            e.preventDefault();
        }
    });

    // Form validation
    document.querySelector('.customer-form').addEventListener('submit', function (e) {
        const name = document.getElementById('customer_name').value.trim();
        const email = document.getElementById('customer_email').value.trim();
        const phone = document.getElementById('customer_phone').value.trim();

        if (!name || !email || !phone) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return false;
        }
    });
</script>
@endsection