@extends('layout.app')

@section('title', 'Confirm Booking - ' . $service->name)

@section('content')
<div class="container">

    {{-- Header --}}
    <div class="header">
        <h1>âœ… Confirm Your Booking</h1>
        <p>Review your appointment details before confirming</p>
    </div>

    {{-- Progress --}}
    <div class="booking-progress">
        <div class="progress-step completed">
            <span class="step-number">âœ“</span>
            <span class="step-label">Select Stylist</span>
        </div>
        <div class="progress-step completed">
            <span class="step-number">âœ“</span>
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
                <h3>ðŸ’… Service Details</h3>
                <div class="detail-row"><span class="label">Service:</span> <span class="value">{{ $service->name }}</span></div>
                <div class="detail-row"><span class="label">Category:</span> <span class="value">{{ $service->category_name ?? 'Beauty Service' }}</span></div>
                <div class="detail-row"><span class="label">Duration:</span> <span class="value">{{ $service->duration }} minutes</span></div>
                <div class="detail-row"><span class="label">Price:</span> <span class="value price-highlight">${{ number_format($service->price, 2) }}</span></div>
            </div>

            <div class="appointment-details-summary">
                <h3>ðŸ“… Appointment Details</h3>
                <div class="detail-row"><span class="label">Stylist:</span> <span class="value">{{ $stylist->name }}</span></div>
                <div class="detail-row"><span class="label">Date:</span> <span class="value">{{ date('l, F j, Y', strtotime($selectedDate)) }}</span></div>
                <div class="detail-row"><span class="label">Time:</span> <span class="value">{{ date('g:i A', strtotime($selectedTime)) }}</span></div>
                <div class="detail-row"><span class="label">Ends:</span> <span class="value">{{ date('g:i A', strtotime($selectedTime . ' +' . $service->duration . ' minutes')) }}</span></div>
            </div>
        </div>

        {{-- What to expect --}}
        <div class="service-description-summary">
            <h3>â„¹ï¸ What to Expect</h3>
            <p>{{ $service->description ?? 'Professional service tailored to your needs.' }}</p>
            @if(!empty($service->benefits))
                <h4>Benefits:</h4>
                <p>{{ $service->benefits }}</p>
            @endif
        </div>

        {{-- Styles for form/errors/buttons --}}
        <style>
            .bf-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px}
            .bf-pills{display:flex;gap:8px;margin:6px 0 12px}
            .bf-pill{border:1px solid #e5e7eb;border-radius:999px;padding:10px 14px;cursor:pointer;color:#0f172a;background:#fff}
            .bf-pill input{display:none}
            .bf-pill.active{background:#0f172a;color:#fff;border-color:#0f172a}
            .bf-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
            @media(max-width:640px){.bf-grid{grid-template-columns:1fr}}
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
        </style>

        {{-- Customer Information / Form --}}
        <div class="customer-info-section">
            <h3>ðŸ“‹ Who is this booking for?</h3>

            <div class="bf-card" id="bf-card">

                {{-- non-validation errors (like slot clash, business hours) --}}
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('booking.store') }}">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                    <input type="hidden" name="stylist_id" value="{{ $stylist->id }}">
                    <input type="hidden" name="booking_date" value="{{ $selectedDate }}">
                    <input type="hidden" name="booking_time" value="{{ $selectedTime }}">

                    {{-- pill tabs --}}
                    <div class="bf-pills">
                        <label class="bf-pill {{ old('booking_for','self')==='self' ? 'active':'' }}">
                            <input type="radio" name="booking_for" value="self" {{ old('booking_for','self')==='self' ? 'checked':'' }}>
                            Book for yourself
                        </label>
                        <label class="bf-pill {{ old('booking_for')==='other' ? 'active':'' }}">
                            <input type="radio" name="booking_for" value="other" {{ old('booking_for')==='other' ? 'checked':'' }}>
                            Book for someone else
                        </label>
                    </div>

                    {{-- SELF --}}
                    @auth
                    <div id="bf-self" style="display: {{ old('booking_for','self')==='self' ? 'block' : 'none' }}">
                        <div class="bf-grid">
                            <div>
                                <div class="bf-label">Full name</div>
                                <input class="bf-input" value="{{ auth()->user()->name }}" disabled>
                            </div>
                            <div>
                                <div class="bf-label">Email</div>
                                <input class="bf-input" value="{{ auth()->user()->email }}" disabled>
                            </div>
                            <div style="grid-column:1/-1">
                                <div class="bf-label">Phone number *</div>
                                <input id="customer_phone" name="customer_phone"
                                       class="bf-input @error('customer_phone') is-invalid @enderror"
                                       value="{{ old('customer_phone', auth()->user()->phone ?? '') }}">
                                @error('customer_phone') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    @endauth

                    {{-- OTHER --}}
                    <div id="bf-other" style="display: {{ old('booking_for')==='other' ? 'block' : 'none' }}">
                        <div class="bf-grid">
                            <div>
                                <div class="bf-label">Full name *</div>
                                <input name="other_name"
                                       class="bf-input @error('other_name') is-invalid @enderror"
                                       value="{{ old('other_name') }}">
                                @error('other_name') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <div class="bf-label">Email *</div>
                                <input name="other_email"
                                       class="bf-input @error('other_email') is-invalid @enderror"
                                       value="{{ old('other_email') }}">
                                @error('other_email') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                            <div style="grid-column:1/-1">
                                <div class="bf-label">Phone number *</div>
                                <input name="other_phone"
                                       class="bf-input @error('other_phone') is-invalid @enderror"
                                       value="{{ old('other_phone') }}">
                                @error('other_phone') <p class="error-text">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:8px">
                        <div class="bf-label">Special requests (optional)</div>
                        <textarea name="special_requests" class="bf-input bf-textarea">{{ old('special_requests') }}</textarea>
                    </div>

                    <div class="bf-actions">
                        <button type="submit" class="bf-btn-primary">âœ… Confirm Booking</button>
                        <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" class="bf-btn-ghost">ðŸ”„ Modify Time</a>
                    </div>

                    <p class="bf-help">By confirming this booking, you agree to our terms and conditions.</p>
                </form>
            </div>
        </div>

        {{-- Back link --}}
        <div class="navigation-links" style="margin-top:12px">
            <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $selectedDate }}" class="back-link">â† Back to Time Selection</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const card = document.getElementById('bf-card');
    const radios = card.querySelectorAll('input[name="booking_for"]');
    const selfBox = document.getElementById('bf-self');
    const otherBox = document.getElementById('bf-other');

    function toggleUI() {
        const mode = card.querySelector('input[name="booking_for"]:checked').value;
        if (selfBox) selfBox.style.display = (mode === 'self') ? 'block' : 'none';
        otherBox.style.display = (mode === 'other') ? 'block' : 'none';
        card.querySelectorAll('.bf-pill').forEach(p => p.classList.toggle('active', p.querySelector('input').checked));
    }
    radios.forEach(r => r.addEventListener('change', toggleUI));
    toggleUI();

    const hadErrors = @json($errors->any());
    const oldMode = @json(old('booking_for', 'self'));
    const hasOtherErrors = @json($errors->has('other_name') || $errors->has('other_email') || $errors->has('other_phone'));

    if (hasOtherErrors) {
        document.querySelector('input[name="booking_for"][value="other"]').checked = true;
    } else if (oldMode === 'other') {
        document.querySelector('input[name="booking_for"][value="other"]').checked = true;
    } else {
        document.querySelector('input[name="booking_for"][value="self"]').checked = true;
    }
    toggleUI();

    // Scroll to form if there are errors
    if (hadErrors) {
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
@endsection