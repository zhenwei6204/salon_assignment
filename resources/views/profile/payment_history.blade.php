@extends('layout.app')
@section('title', 'Payment History')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="page-header">
        <h1 class="page-title">Payment History</h1>
        <p class="page-subtitle">Track all your payments and transactions</p>
        
@if(isset($user_details) && $user_details)
    <div class="customer-info-card">
        <div class="customer-header">
            <h3 class="customer-title">Customer Information</h3>
            <div class="service-badge">
                <span class="badge-icon">🔗</span>
                <span class="badge-text">External User Service</span>
            </div>
        </div>
        
        <div class="customer-details-wrapper">
            <div class="customer-details-grid">
                <div class="customer-detail-item">
                    <div class="detail-icon">👤</div>
                    <div class="detail-content">
                        <span class="detail-label">Customer ID</span>
                        <span class="detail-value customer-id">{{ $user_details['id'] ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="customer-detail-item">
                    <div class="detail-icon">📝</div>
                    <div class="detail-content">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value customer-name">{{ $user_details['name'] ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="customer-detail-item">
                    <div class="detail-icon">📧</div>
                    <div class="detail-content">
                        <span class="detail-label">Email Address</span>
                        <span class="detail-value customer-email">{{ $user_details['email'] ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="customer-detail-item">
                    <div class="detail-icon">🏷️</div>
                    <div class="detail-content">
                        <span class="detail-label">Customer Type</span>
                        <span class="detail-value">
                            <span class="customer-role-badge role-{{ strtolower($user_details['role'] ?? 'user') }}">
                                {{ ($user_details['roles'] ) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

   @endif

    <!-- Payment Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>${{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
                <p>Total Spent</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3>{{ $stats['completed_count'] ?? 0 }}</h3>
                <p>Completed Payments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <h3>{{ $stats['pending_count'] ?? 0 }}</h3>
                <p>Pending Payments</p>
            </div>
        </div>
        @if(isset($stats['failed_count']))
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
                <h3>{{ $stats['failed_count'] ?? 0 }}</h3>
                <p>Failed Payments</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('payments.history') }}" class="filters-form">
            @if(request('use_api'))
                <input type="hidden" name="use_api" value="1">
            @endif
            
            <div class="filters-row">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="q" value="{{ $filters['q'] ?? '' }}" 
                        placeholder="Search by payment ref, booking ref, or service...">
                </div>

                <div class="filter-group">
                    <label for="method">Payment Method</label>
                    <select id="method" name="method">
                        <option value="">All Methods</option>
                        <option value="cash" {{ ($filters['method'] ?? '') == 'cash' ? 'selected' : '' }}>Cash Payment</option>
                        <option value="credit_card" {{ ($filters['method'] ?? '') == 'credit_card' ? 'selected' : '' }}>Credit/Debit Card</option>
                        <option value="paypal" {{ ($filters['method'] ?? '') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="bank_transfer" {{ ($filters['method'] ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>

            <div class="filters-row">
                <div class="filter-group">
                    <label for="from">From Date</label>
                    <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>

                <div class="filter-group">
                    <label for="to">To Date</label>
                    <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="{{ route('payments.history', request()->only('use_api')) }}" class="clear-btn">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments List -->
    <div class="payments-section">
        @if($payments->count() > 0)
            <div class="payments-grid">
                @foreach($payments as $payment)
                    @php
                        // Handle both object and array data (for API responses)
                        $paymentObj = is_array($payment) ? (object) $payment : $payment;
                        $booking = null;
                        $service = null;
                        $stylist = null;
                        
                        if (is_array($payment) && isset($payment['booking'])) {
                            $booking = (object) $payment['booking'];
                            $service = isset($payment['booking']['service']) ? (object) $payment['booking']['service'] : null;
                            $stylist = isset($payment['booking']['stylist']) ? (object) $payment['booking']['stylist'] : null;
                        } elseif (is_object($payment) && isset($payment->booking)) {
                            $booking = $payment->booking;
                            $service = $payment->booking->service ?? null;
                            $stylist = $payment->booking->stylist ?? null;
                        }
                        
                        // Get payment method with fallback
                        $paymentMethod = $paymentObj->payment_method ?? 'default';
                        $formattedMethod = $paymentObj->formatted_payment_method ?? 
                            match($paymentMethod) {
                                'cash' => 'Cash Payment at Salon',
                                'credit_card' => 'Credit/Debit Card',
                                'paypal' => 'PayPal',
                                'bank_transfer' => 'Bank Transfer',
                                default => 'Payment Method'
                            };
                    @endphp
                    
                    <div class="payment-card">
                        <div class="payment-header">
                            <div class="payment-method">
                                <span class="method-icon">
                                    @switch($paymentMethod)
                                        @case('cash')
                                            💵
                                            @break
                                        @case('credit_card')
                                            💳
                                            @break
                                        @case('paypal')
                                            🌐
                                            @break
                                        @case('bank_transfer')
                                            🏦
                                            @break
                                        @default
                                            💰
                                    @endswitch
                                </span>
                                <div class="method-details">
                                    <h4>{{ $formattedMethod }}</h4>
                                </div>
                            </div>
                            <div class="payment-status">
                                @php
                                    $status = $paymentObj->status ?? 'pending';
                                    $statusColor = match($status) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="status-badge status-{{ $statusColor }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </div>
                        </div>

                        <div class="payment-body">
                            <div class="payment-amount">
                                <span class="amount">${{ number_format($paymentObj->amount ?? 0, 2) }}</span>
                            </div>

                            <div class="payment-details">
                                <div class="detail-row">
                                    <span class="label">Service:</span>
                                    <span class="value">{{ $service->name ?? 'N/A' }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Stylist:</span>
                                    <span class="value">{{ $stylist->name ?? 'N/A' }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Booking Date:</span>
                                    <span class="value">
                                        @if($booking && isset($booking->booking_date))
                                            {{ is_string($booking->booking_date) ? date('M j, Y', strtotime($booking->booking_date)) : $booking->booking_date->format('M j, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Payment Date:</span>
                                    <span class="value">
                                        @if(isset($paymentObj->created_at))
                                            {{ is_string($paymentObj->created_at) ? date('M j, Y \a\t g:i A', strtotime($paymentObj->created_at)) : $paymentObj->created_at->format('M j, Y \a\t g:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                @if(isset($paymentObj->id))
                                    <div class="detail-row">
                                        <span class="label">Payment ID:</span>
                                        <span class="value">#{{ $paymentObj->id }}</span>
                                    </div>
                                @endif
                                @if(isset($booking->booking_reference))
                                    <div class="detail-row">
                                        <span class="label">Booking Ref:</span>
                                        <span class="value">{{ $booking->booking_reference }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="payment-footer">
                            <div class="payment-actions">
                                @if($booking && isset($booking->booking_reference))
                                    <a href="{{ route('bookings.index') }}?q={{ $booking->booking_reference }}" 
                                       class="view-booking-btn">
                                        View Booking
                                    </a>
                                @endif
                                
                                @if(isset($paymentObj->id) && $status === 'completed' && $booking)
                                    @php
                                        $bookingId = is_array($booking) ? $booking['id'] : $booking->id;
                                    @endphp
                                    <a href="{{ route('refunds.create', ['booking_id' => $bookingId]) }}" 
                                       class="refund-btn">
                                        Request Refund
                                    </a>
                                @endif
                                
                                @if($status === 'pending' && $paymentMethod === 'cash')
                                    <span class="pending-note">Pay at salon</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                @if(method_exists($payments, 'links'))
                    {{ $payments->appends(request()->query())->links() }}
                @endif
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">💳</div>
                <h3>No payment history found</h3>
                <p>
                    @if(array_filter($filters ?? []))
                        No payments match your current filters. 
                        <a href="{{ route('payments.history', request()->only('use_api')) }}" class="clear-filters-link">Clear filters</a> to see all payments.
                    @else
                        You haven't made any payments yet. 
                        <a href="{{ route('services.index') }}" class="book-service-link">Book a service</a> to get started.
                    @endif
                </p>
            </div>
        @endif
    </div>

   

    <!-- Navigation Links -->
    <div class="page-navigation">
        <a href="{{ route('bookings.index') }}" class="nav-link">
            <i class="fas fa-calendar-alt"></i>
            My Bookings
        </a>
        <a href="{{ route('profile.show') }}" class="nav-link">
            <i class="fas fa-user"></i>
            My Profile
        </a>
    </div>
</div>

<style>
:root {
    --primary-black: #1a1a1a;
    --soft-black: #333333;
    --medium-gray: #e0e0e0;
    --light-gray: #f8f9fa;
    --border-color: #dee2e6;
    --transition: all 0.3s ease;
}

.customer-info-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin: 1.5rem 0 3rem 0;
    transition: var(--transition);
    border-left: 4px solid var(--primary-black);
}

.customer-info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.customer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--light-gray);
    border-bottom: 1px solid var(--border-color);
}

.customer-title {
    color: var(--primary-black);
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0;
}

.service-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #d4edda;
    color: #155724;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-icon {
    font-size: 1rem;
}

.badge-text {
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.customer-details-wrapper {
    padding: 1.5rem;
}

.customer-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.customer-detail-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: 10px;
    transition: var(--transition);
    border: 2px solid transparent;
}

.customer-detail-item:hover {
    background: #fff;
    border-color: var(--medium-gray);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.detail-icon {
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.detail-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.detail-label {
    font-size: 0.85rem;
    color: var(--soft-black);
    opacity: 0.7;
    font-weight: 500;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    color: var(--primary-black);
    font-weight: 600;
    font-size: 1rem;
}

.customer-id {
    font-family: 'Courier New', monospace;
    color: #1565c0;
}

.customer-name {
    color: var(--primary-black);
}

.customer-email {
    color: #155724;
    word-break: break-word;
}

/* Customer Role Badge - Enhanced */
.customer-role-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.role-user {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    color: #1565c0;
}

.role-premium {
    background: linear-gradient(135deg, #fff3e0, #ffcc02);
    color: #f57c00;
}

.role-vip {
    background: linear-gradient(135deg, #f3e5f5, #e1bee7);
    color: #7b1fa2;
}

.role-admin {
    background: linear-gradient(135deg, #fce4ec, #f8bbd9);
    color: #c2185b;
}

/* Existing styles with improvements */
.refund-btn {
    background: #f59e0b;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transition);
}

.refund-btn:hover {
    background: #d97706;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-black);
    margin-bottom: 0.5rem;
}

.page-subtitle {
    font-size: 1.1rem;
    color: var(--soft-black);
    opacity: 0.8;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    font-size: 2.5rem;
    width: 60px;
    text-align: center;
}

.stat-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-black);
    margin-bottom: 0.25rem;
}

.stat-content p {
    color: var(--soft-black);
    opacity: 0.7;
    font-size: 0.9rem;
}

.filters-section {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.filters-row:last-child {
    margin-bottom: 0;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--primary-black);
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 2px solid var(--medium-gray);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--primary-black);
}

.filter-actions {
    display: flex;
    align-items: end;
    gap: 1rem;
}

.filter-btn,
.clear-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.filter-btn {
    background: var(--primary-black);
    color: white;
}

.filter-btn:hover {
    background: var(--soft-black);
}

.clear-btn {
    background: var(--medium-gray);
    color: var(--soft-black);
}

.clear-btn:hover {
    background: var(--border-color);
}

.payments-grid {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.payment-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: var(--transition);
}

.payment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--light-gray);
    border-bottom: 1px solid var(--border-color);
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.method-icon {
    font-size: 2rem;
}

.method-details h4 {
    font-weight: 600;
    color: var(--primary-black);
    margin-bottom: 0.25rem;
}

.payment-ref {
    font-size: 0.85rem;
    color: var(--soft-black);
    opacity: 0.7;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
}

.status-danger {
    background: #f8d7da;
    color: #721c24;
}

.payment-body {
    padding: 1.5rem;
}

.payment-amount {
    text-align: center;
    margin-bottom: 1.5rem;
}

.amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-black);
}

.payment-details {
    display: grid;
    gap: 0.75rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-row .label {
    font-weight: 600;
    color: var(--soft-black);
}

.detail-row .value {
    color: var(--primary-black);
}

.payment-footer {
    padding: 1rem 1.5rem;
    background: var(--light-gray);
    border-top: 1px solid var(--border-color);
}

.payment-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.view-booking-btn {
    background: var(--primary-black);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transition);
}

.view-booking-btn:hover {
    background: var(--soft-black);
}

.pending-note {
    font-size: 0.85rem;
    color: #856404;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: var(--primary-black);
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--soft-black);
    opacity: 0.8;
    line-height: 1.6;
}

.clear-filters-link,
.book-service-link {
    color: var(--primary-black);
    font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid var(--primary-black);
}

.page-navigation {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--primary-black);
    text-decoration: none;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: var(--transition);
    font-weight: 500;
}

.nav-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-row {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .payment-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .payment-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-navigation {
        flex-direction: column;
        align-items: center;
    }
    
    .data-source-indicator {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .api-test-buttons {
        flex-direction: column;
    }

    .user-service-info {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    margin: 1rem 0;
}

.user-service-info h3 {
    color: var(--primary-black);
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.user-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.user-detail {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
    border-bottom: 1px solid var(--border-color);
}

.user-detail .label {
    font-weight: 600;
    color: var(--soft-black);
}

.user-detail .value {
    color: var(--primary-black);
}

.role-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-user {
    background: #e3f2fd;
    color: #1565c0;
}

.role-admin {
    background: #fce4ec;
    color: #c2185b;
}

.role-premium {
    background: #fff3e0;
    color: #f57c00;
}

.role-vip {
    background: #f3e5f5;
    color: #7b1fa2;
}

.role-regular {
    background: #f5f5f5;
    color: #424242;
}
}
</style>
@endsection