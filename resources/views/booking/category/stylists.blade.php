@extends('layout.app')

@section('title', 'Select Stylist - ' . $service->name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>💇‍♀️ Select Your Stylist</h1>
        <p>Choose from our available professional stylists</p>
    </div>

    <!-- Service Info Banner -->
    <div class="service-info-banner">
        <div class="service-summary">
            <h3>Selected Service: {{ $service->name }}</h3>
            <div class="service-meta">
                <span class="price">${{ number_format($service->price, 2) }}</span>
                <span class="duration">⏱️ {{ $service->duration }} mins</span>
            </div>
        </div>
    </div>

    <!-- Booking Progress -->
    <div class="booking-progress">
        <div class="progress-step active">
            <span class="step-number">1</span>
            <span class="step-label">Select Stylist</span>
        </div>
        <div class="progress-step">
            <span class="step-number">2</span>
            <span class="step-label">Choose Date & Time</span>
        </div>
        <div class="progress-step">
            <span class="step-number">3</span>
            <span class="step-label">Confirm Booking</span>
        </div>
    </div>

    <!-- Stylists Section -->
    <div class="stylists-section">
        <h2 class="section-title">Available Stylists</h2>
        
        @if(count($stylists) > 0)
            <div class="stylists-grid">
                @foreach($stylists as $stylist)
                    <div class="stylist-card">
                        <div class="stylist-avatar">
                            <span class="avatar-icon">👩‍💼</span>
                        </div>
                        <div class="stylist-info">
                            <h3>{{ $stylist['name'] }}</h3>
                            <p class="stylist-title">{{ $stylist['title'] ?? 'Professional Stylist' }}</p>
                            <p class="stylist-experience">
                                📅 {{ $stylist['experience_years'] ?? '5' }} years experience
                            </p>
                            <p class="stylist-specializations">
                                🎯 {{ $stylist['specializations'] ?? 'Hair Styling, Color' }}
                            </p>
                            <div class="stylist-rating">
                                ⭐ {{ number_format($stylist['rating'] ?? 4.8, 1) }}/5.0
                                <span class="reviews-count">({{ $stylist['review_count'] ?? '150' }} reviews)</span>
                            </div>
                        </div>
                        <div class="stylist-actions">
                            <a href="{{ route('booking.select.time', [$service->id, $stylist['id']]) }}" 
                               class="select-stylist-btn">
                                Select This Stylist
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-results">
                <h3>No Stylists Available</h3>
                <p>Sorry, no stylists are currently available for this service. Please try again later or contact us directly.</p>
            </div>
        @endif
    </div>

    <!-- Back Link -->
    <div class="navigation-links">
        <a href="{{ route('services.show', $service->id) }}" class="back-link">
            ← Back to Service Details
        </a>
    </div>
</div>
@endsection