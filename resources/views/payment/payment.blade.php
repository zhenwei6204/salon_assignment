@extends('layout.app')

@section('title', 'Payment - Complete Your Booking')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>Payment</h1>
        <p>Complete your booking payment</p>
    </div>

    <!-- Display error messages -->
    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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
        <div class="progress-step completed">
            <span class="step-number">✓</span>
            <span class="step-label">Confirm Details</span>
        </div>
        <div class="progress-step active">
            <span class="step-number">4</span>
            <span class="step-label">Payment</span>
        </div>
    </div>

    <!-- Booking Summary -->
    <div class="booking-summary">
        <h2 class="section-title">Booking Summary</h2>

        <div class="summary-card">
            <div class="service-details-summary">
                <h3>Service Details</h3>
                <div class="detail-row">
                    <span class="label">Service:</span>
                    <span class="value">{{ $service->name }}</span>
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
                <h3>Appointment Details</h3>
                <div class="detail-row">
                    <span class="label">Stylist:</span>
                    <span class="value">{{ $bookingDetails['stylist']->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ date('l, F j, Y', strtotime($bookingDetails['selectedDate'])) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Time:</span>
                    <span class="value">{{ date('g:i A', strtotime($bookingDetails['selectedTime'])) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Customer:</span>
                    <span class="value">{{ $bookingDetails['customer_name'] }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-section">
            <h3>Payment Method</h3>
            <form method="POST" action="{{ route('booking.payment.process', $service->id) }}" class="payment-form" id="paymentForm">
                @csrf

                <!-- Payment Method Selection -->
                <div class="payment-methods">
                    @foreach($availablePaymentMethods as $method)
                    <div class="payment-option">
                        <input type="radio" id="{{ $method['key'] }}" name="payment_method" value="{{ $method['key'] }}" 
                               {{ old('payment_method') == $method['key'] ? 'checked' : '' }} required>
                        <label for="{{ $method['key'] }}" class="payment-label">
                            <div class="payment-icon">
                                @switch($method['key'])
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
                            </div>
                            <div class="payment-details">
                                <strong>{{ $method['name'] }}</strong>
                                @switch($method['key'])
                                    @case('cash')
                                        <small>Pay when you arrive at the salon</small>
                                        @break
                                    @case('credit_card')
                                        <small>Secure credit/debit card payment</small>
                                        @break
                                    @case('paypal')
                                        <small>Pay with your PayPal account</small>
                                        @break
                                    @case('bank_transfer')
                                        <small>Direct bank transfer payment</small>
                                        @break
                                @endswitch
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <!-- Dynamic Payment Details Forms -->
                
                <!-- Credit Card Details -->
                <div id="credit_card_details" class="payment-details-form" style="display: none;">
                    <h4>Credit Card Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cardholder_name">Cardholder Name</label>
                            <input type="text" id="cardholder_name" name="cardholder_name" 
                                   value="{{ old('cardholder_name', $bookingDetails['customer_name']) }}">
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" 
                                   value="{{ old('card_number') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" 
                                   value="{{ old('expiry_date') }}">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" 
                                   value="{{ old('cvv') }}">
                        </div>
                    </div>
                </div>

                <!-- PayPal Details -->
                <div id="paypal_details" class="payment-details-form" style="display: none;">
                    <h4>PayPal Information</h4>
                    <div class="form-group">
                        <label for="paypal_email">PayPal Email</label>
                        <input type="email" id="paypal_email" name="paypal_email" 
                               value="{{ old('paypal_email', $bookingDetails['customer_email']) }}">
                    </div>
                </div>

                <!-- Bank Transfer Details -->
                <div id="bank_transfer_details" class="payment-details-form" style="display: none;">
                    <h4>Bank Transfer Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="account_holder_name">Account Holder Name</label>
                            <input type="text" id="account_holder_name" name="account_holder_name" 
                                   value="{{ old('account_holder_name', $bookingDetails['customer_name']) }}">
                        </div>
                        <div class="form-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" 
                                   value="{{ old('bank_name') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" id="account_number" name="account_number" 
                                   value="{{ old('account_number') }}">
                        </div>
                        <div class="form-group">
                            <label for="routing_number">Routing Number (Optional)</label>
                            <input type="text" id="routing_number" name="routing_number" 
                                   value="{{ old('routing_number') }}">
                        </div>
                    </div>
                </div>

                <!-- Cash Payment Info -->
                <div id="cash_details" class="payment-details-form" style="display: none;">
                    <div class="cash-info">
                        <h4>Cash Payment</h4>
                        <p>You can pay with cash when you arrive at the salon. Please bring the exact amount or have change ready.</p>
                        <p><strong>Amount to Pay: ${{ number_format($service->price, 2) }}</strong></p>
                    </div>
                </div>

                <!-- Payment Total -->
                <div class="payment-total">
                    <div class="total-row">
                        <span class="total-label">Service Cost:</span>
                        <span class="total-amount">${{ number_format($service->price, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Processing Fee:</span>
                        <span class="total-amount">$0.00</span>
                    </div>
                    <hr>
                    <div class="total-row final-total">
                        <span class="total-label">Total Amount:</span>
                        <span class="total-amount">${{ number_format($service->price, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Actions -->
                <div class="payment-actions">
                    <div class="action-buttons">
                        <button type="submit" class="complete-payment-btn">
                            Complete Booking
                        </button>
                        <a href="{{ route('booking.confirmation', [$service, $bookingDetails['stylist']]) }}?date={{ $bookingDetails['selectedDate'] }}&time={{ $bookingDetails['selectedTime'] }}" 
                           class="back-payment-btn">
                            Back to Confirmation
                        </a>
                    </div>
                    <p class="payment-note">
                        By completing this booking, you agree to our terms and conditions.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment method change
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const detailsForms = document.querySelectorAll('.payment-details-form');

    function showPaymentDetails() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        
        // Hide all detail forms
        detailsForms.forEach(form => {
            form.style.display = 'none';
        });

        // Show selected method's details
        if (selectedMethod) {
            const detailsForm = document.getElementById(selectedMethod.value + '_details');
            if (detailsForm) {
                detailsForm.style.display = 'block';
            }
        }
    }

    paymentMethods.forEach(method => {
        method.addEventListener('change', showPaymentDetails);
    });

    // Show details for initially selected method
    showPaymentDetails();

    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        
        if (!selectedMethod) {
            e.preventDefault();
            alert('Please select a payment method.');
            return;
        }

        // Validate based on selected payment method
        let isValid = true;
        let errorMessage = '';

        switch (selectedMethod.value) {
            case 'credit_card':
                const cardNumber = document.getElementById('card_number').value.trim();
                const expiryDate = document.getElementById('expiry_date').value.trim();
                const cvv = document.getElementById('cvv').value.trim();
                const cardholderName = document.getElementById('cardholder_name').value.trim();

                if (!cardNumber || !expiryDate || !cvv || !cardholderName) {
                    isValid = false;
                    errorMessage = 'Please fill in all credit card details.';
                }
                break;

            case 'paypal':
                const paypalEmail = document.getElementById('paypal_email').value.trim();
                if (!paypalEmail) {
                    isValid = false;
                    errorMessage = 'Please enter your PayPal email.';
                }
                break;

            case 'bank_transfer':
                const accountHolderName = document.getElementById('account_holder_name').value.trim();
                const bankName = document.getElementById('bank_name').value.trim();
                const accountNumber = document.getElementById('account_number').value.trim();

                if (!accountHolderName || !bankName || !accountNumber) {
                    isValid = false;
                    errorMessage = 'Please fill in all bank transfer details.';
                }
                break;
        }

        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });

    // Format card number input
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
        });
    }

    // Format expiry date input
    const expiryInput = document.getElementById('expiry_date');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0,2) + '/' + value.substring(2,4);
            }
            e.target.value = value;
        });
    }
});
</script>

<style>
.payment-section {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-top: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-label {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: #2c5aa0;
    background: #f8f9ff;
    box-shadow: 0 4px 12px rgba(44, 90, 160, 0.15);
}

.payment-icon {
    font-size: 2rem;
    margin-right: 1rem;
    min-width: 50px;
    text-align: center;
}

.payment-details-form {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.cash-info {
    text-align: center;
    padding: 1rem;
}

.payment-total {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.final-total {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c5aa0;
    border-top: 1px solid #ddd;
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

.payment-actions {
    text-align: center;
    margin-top: 2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 1rem;
}

.complete-payment-btn, .back-payment-btn {
    padding: 1rem 2rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.complete-payment-btn {
    background: #28a745;
    color: white;
}

.complete-payment-btn:hover {
    background: #218838;
}

.back-payment-btn {
    background: #6c757d;
    color: white;
}

.back-payment-btn:hover {
    background: #545b62;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
    
    .form-row {
        flex-direction: column;
    }
}
</style>
@endsection