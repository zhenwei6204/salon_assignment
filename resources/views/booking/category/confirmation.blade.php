@extends('layout.app')

@section('title', 'Confirm Booking - ' . $service->name)

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="header">
        <h1>✅ Confirm Your Booking</h1>
        <p>Review your appointment details before confirming</p>
    </div>

    {{-- Progress --}}
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

    {{-- Summary --}}
    <div class="booking-summary">
        <h2 class="section-title">Appointment Summary</h2>

        <div class="summary-card">
            <div class="service-details-summary">
                <h3>💅 Service Details</h3>
                <div class="detail-row"><span class="label">Service:</span> <span class="value">{{ $service->name }}</span></div>
                <div class="detail-row"><span class="label">Category:</span> <span class="value">{{ $service->category_name ?? 'Beauty Service' }}</span></div>
                <div class="detail-row"><span class="label">Duration:</span> <span class="value">{{ $service->duration }} minutes</span></div>
                <div class="detail-row"><span class="label">Price:</span> <span class="value price-highlight">${{ number_format($service->price, 2) }}</span></div>
            </div>

            <div class="appointment-details-summary">
                <h3>📅 Appointment Details</h3>
                <div class="detail-row"><span class="label">Stylist:</span> <span class="value">{{ $stylist->name }}</span></div>
                <div class="detail-row"><span class="label">Date:</span> <span class="value">{{ date('l, F j, Y', strtotime($selectedDate)) }}</span></div>
                <div class="detail-row"><span class="label">Time:</span> <span class="value">{{ date('g:i A', strtotime($selectedTime)) }}</span></div>
                <div class="detail-row"><span class="label">Ends:</span> <span class="value">{{ date('g:i A', strtotime($selectedTime . ' +' . $service->duration . ' minutes')) }}</span></div>
            </div>
        </div>

        {{-- What to expect --}}
        <div class="service-description-summary">
            <h3>ℹ️ What to Expect</h3>
            <p>{{ $service->description ?? 'Professional service tailored to your needs.' }}</p>
            @if(!empty($service->benefits))
                <h4>Benefits:</h4>
                <p>{{ $service->benefits }}</p>
            @endif
        </div>

        {{-- Styles for form/errors/buttons --}}
        <style>
            .bf-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px}
            .bf-label{font-size:13px;color:#374151;margin:6px 0 4px}
            .bf-input{border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;width:100%;outline:0;background:#fff}
            .bf-input:focus{border-color:#0f172a;box-shadow:0 0 0 3px rgba(17,24,39,.08)}
            .bf-textarea{min-height:92px;resize:vertical}
            .bf-actions{display:flex;gap:12px;justify-content:center;margin-top:16px}
            .bf-btn-primary{background:#22c55e;color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:600;cursor:pointer;transition:.2s}
            .bf-btn-primary:hover{background:#16a34a}
            .bf-btn-ghost{background:#3b82f6;color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none;display:inline-block}
            .bf-btn-ghost:hover{background:#2563eb}
            .bf-help{font-size:12px;color:#6b7280;margin-top:4px;text-align:center}

            .alert{padding:10px 12px;border-radius:10px;margin:10px 0;font-size:14px}
            .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}

            .is-invalid{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.1)}
            .error-text{color:#ef4444;font-size:12px;margin-top:6px}

            .customer-details{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
            @media(max-width:640px){.customer-details{grid-template-columns:1fr}}
        </style>

        {{-- Customer Information / Form --}}
        <div class="customer-info-section">
            <h3>📋 Confirm Your Details</h3>

            <div class="bf-card">

                {{-- non-validation errors (like slot clash, business hours) --}}
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @guest
                    <div class="alert alert-error">
                        You must be logged in to make a booking. <a href="{{ route('login') }}">Please log in</a> to continue.
                    </div>
                @else
                    <form method="POST" action="{{ route('booking.store') }}">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">
                        <input type="hidden" name="stylist_id" value="{{ $stylist->id }}">
                        <input type="hidden" name="booking_date" value="{{ $selectedDate }}">
                        <input type="hidden" name="booking_time" value="{{ $selectedTime }}">

                        {{-- Customer details (pre-filled from auth user) --}}
                        <div class="customer-details">
                            <div>
                                <div class="bf-label">Full Name</div>
                                <input class="bf-input" value="{{ auth()->user()->name }}" disabled>
                            </div>
                            <div>
                                <div class="bf-label">Email</div>
                                <input class="bf-input" value="{{ auth()->user()->email }}" disabled>
                            </div>
                        </div>

                        <div style="margin-bottom:16px">
                            <div class="bf-label">Phone Number *</div>
                            <input id="customer_phone" name="customer_phone"
                                   class="bf-input @error('customer_phone') is-invalid @enderror"
                                   value="{{ old('customer_phone', auth()->user()->phone ?? '') }}"
                                   placeholder="Enter your phone number"
                                   required>
                            @error('customer_phone') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div style="margin-bottom:16px">
                            <div class="bf-label">Special Requests (Optional)</div>
                            <textarea name="special_requests" class="bf-input bf-textarea" placeholder="Any special requirements or requests?">{{ old('special_requests') }}</textarea>
                        </div>

                        <div class="bf-actions">
                            <button type="submit" class="bf-btn-primary">✅ Confirm Booking</button>
                            <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" class="bf-btn-ghost">🔄 Modify Time</a>
                        </div>

                        <p class="bf-help">By confirming this booking, you agree to our terms and conditions.</p>
                    </form>
                @endguest
            </div>
        </div>

        {{-- Back link --}}
        <div class="navigation-links" style="margin-top:12px">
            <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" class="back-link">← Back to Time Selection</a>
        </div>
    </div>
</div>
@endsection