@extends('layout.app')

@section('title', 'Select Date & Time - ' . $service->name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>📅 Select Date & Time</h1>
        <p>Choose your preferred appointment time</p>
    </div>

    <!-- Service Info Banner -->
    <div class="service-info-banner">
        <div class="service-summary">
            <h3>Service: {{ $service->name }}</h3>
            <div class="service-meta">
                <span class="price">${{ number_format($service->price, 2) }}</span>
                <span class="duration">⏱️ {{ $service->duration }} mins</span>
                <span class="stylist">👩‍💼 {{ $stylist->name }}</span>
            </div>
        </div>
    </div>

    <!-- Booking Progress -->
    <div class="booking-progress">
        <div class="progress-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Select Stylist</span>
        </div>
        <div class="progress-step active">
            <span class="step-number">2</span>
            <span class="step-label">Choose Date & Time</span>
        </div>
        <div class="progress-step">
            <span class="step-number">3</span>
            <span class="step-label">Confirm Booking</span>
        </div>
    </div>

    <!-- Date & Time Selection -->
    <div class="datetime-selection">
        <!-- Date Selection -->
        <div class="date-selection-section">
            <h2 class="section-title">Select Date</h2>
            <div class="date-picker-container">
                <form method="GET" id="dateForm" action="{{ route('booking.select.time', [$service, $stylist]) }}">
                    <input type="date" 
                           name="date" 
                           value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                           min="{{ date('Y-m-d') }}"
                           max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                           class="date-input"
                           onchange="document.getElementById('dateForm').submit()"> 
                </form>
            </div>
        </div>

        <!-- Time Slots -->
        <div class="time-selection-section">
            <h2 class="section-title">Available Times for {{ date('F j, Y', strtotime($selectedDate)) }}</h2>
            
            @if(count($availableSlots) > 0)
                <div class="time-slots-grid">
                    @foreach($availableSlots as $slot)
                        <div class="time-slot-card">
                            <div class="time-display">
                                {{ date('g:i A', strtotime($slot)) }}
                            </div>
                            <a href="{{ route('booking.confirmation', [$service, $stylist]) }}?date={{ $selectedDate }}&time={{ $slot }}" 
                               class="select-time-btn">
                                Book This Time
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-results">
                    <h3>No Available Times</h3>
                    <p>
                    Sorry, no appointment slots are available for
                    {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                    Please select a different date.
                    </p>

                    <div class="alternative-dates">
                    <h4>Try these alternative dates:</h4>
                    @for ($i = 1; $i <= 3; $i++)
                        @php
                            $alt = \Carbon\Carbon::parse($selectedDate)->addDays($i);
                        @endphp
                        <a href="{{ route('booking.select.time', [$service, $stylist]) }}?date={{ $alt->format('Y-m-d') }}"
                            class="alt-date-link">
                            {{ $alt->format('F j, Y') }}
                        </a>
                    @endfor
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="navigation-links">
        <a href="{{ route('booking.select.stylist', $service) }}" class="back-link">
            ← Back to Stylist Selection
        </a>
    </div>
</div>

<script>
    // Auto-submit form when date changes
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.querySelector('.date-input');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                document.getElementById('dateForm').submit();
            });
        }
    });
</script>
@endsection