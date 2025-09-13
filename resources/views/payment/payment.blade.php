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
                    <span class="value">{{ $booking->booking_date->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Time:</span>
                    <span class="value">{{ $booking->booking_time->format('g:i A') }}</span>
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
            <form action="{{ route('booking.payment.process', ['serviceId' => $service->id]) }}" method="POST" id="payment-form">
                @csrf

                <!-- Payment Method Selection -->
                <div class="payment-methods">
                    @foreach($availablePaymentMethods as $method)
                    <div class="payment-option">
                        <input type="radio" id="{{ $method['key'] }}" name="payment_method" value="{{ $method['key'] }}" 
                               {{ old('payment_method') == $method['key'] ? 'checked' : '' }} required>
                        <label for="{{ $method['key'] }}" class="payment-label">
                            <div class="payment-icon">
                                {{ $method['icon'] }}
                            </div>
                            <div class="payment-details">
                                <strong>{{ $method['name'] }}</strong>
                                <small>{{ $method['description'] }}</small>
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
                            <label for="cardholder_name">Cardholder Name <span class="required">*</span></label>
                            <input type="text" id="cardholder_name" name="cardholder_name" 
                                   class="form-control"
                                   value="{{ old('cardholder_name', $bookingDetails['customer_name']) }}"
                                   placeholder="Name as shown on card">
                            @error('cardholder_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="card_number">Card Number <span class="required">*</span></label>
                            <input type="text" id="card_number" name="card_number" 
                                   class="form-control"
                                   placeholder="1234 5678 9012 3456" 
                                   value="{{ old('card_number') }}"
                                   maxlength="19">
                            @error('card_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date <span class="required">*</span></label>
                            <input type="text" id="expiry_date" name="expiry_date" 
                                   class="form-control"
                                   placeholder="MM/YY" 
                                   value="{{ old('expiry_date') }}"
                                   maxlength="5">
                            @error('expiry_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV <span class="required">*</span></label>
                            <input type="text" id="cvv" name="cvv" 
                                   class="form-control"
                                   placeholder="123" 
                                   value="{{ old('cvv') }}"
                                   maxlength="4">
                            @error('cvv')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- PayPal Details -->
                <div id="paypal_details" class="payment-details-form" style="display: none;">
                    <h4>PayPal Information</h4>
                    <div class="form-group">
                        <label for="paypal_email">PayPal Email <span class="required">*</span></label>
                        <input type="email" id="paypal_email" name="paypal_email" 
                               class="form-control"
                               value="{{ old('paypal_email', $bookingDetails['customer_email']) }}"
                               placeholder="your.email@example.com">
                        @error('paypal_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Bank Transfer Details -->
                <div id="bank_transfer_details" class="payment-details-form" style="display: none;">
                    <h4>Bank Transfer Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="account_holder_name">Account Holder Name <span class="required">*</span></label>
                            <input type="text" id="account_holder_name" name="account_holder_name" 
                                   class="form-control"
                                   value="{{ old('account_holder_name', $bookingDetails['customer_name']) }}"
                                   placeholder="Full name as shown on bank account">
                            @error('account_holder_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="bank_name">Bank Name <span class="required">*</span></label>
                                <select id="bank_name" name="bank_name" class="form-control" required>
                                    <option value="" disabled selected>-- Select a Bank --</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank }}">{{ $bank }}</option>
                                    @endforeach
                                </select>
                            @error('bank_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="account_number">Account Number <span class="required">*</span></label>
                            <input type="text" id="account_number" name="account_number" 
                                   class="form-control"
                                   value="{{ old('account_number') }}"
                                   placeholder="Your account number">
                            @error('account_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="routing_number">Routing Number (Optional)</label>
                            <input type="text" id="routing_number" name="routing_number" 
                                   class="form-control"
                                   value="{{ old('routing_number') }}"
                                   placeholder="Bank routing number">
                            @error('routing_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Bank Transfer Instructions:</strong>
                        <p>After submitting this form, you will receive bank transfer details via email. Please complete the transfer within 24 hours to confirm your booking.</p>
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
                        <a href="{{ route('booking.confirmation', [$booking->service, $booking->stylist]) }}" 
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
    document.getElementById('payment-form').addEventListener('submit', function(e) {
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
                    errorMessage = 'Please fill in all required bank transfer details.';
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

    // Format CVV input (numbers only)
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // Format account number (numbers only)
    const accountNumberInput = document.getElementById('account_number');
    if (accountNumberInput) {
        accountNumberInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }

    // Format routing number (numbers only)
    const routingNumberInput = document.getElementById('routing_number');
    if (routingNumberInput) {
        routingNumberInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
});
</script>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.header {
    text-align: center;
    margin-bottom: 2rem;
}

.header h1 {
    color: #2c5aa0;
    margin-bottom: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.booking-progress {
    display: flex;
    justify-content: center;
    margin-bottom: 3rem;
    gap: 2rem;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.progress-step.completed .step-number {
    background: #28a745;
    color: white;
}

.progress-step.active .step-number {
    background: #2c5aa0;
    color: white;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    font-weight: bold;
}

.booking-summary {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.summary-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.price-highlight {
    color: #2c5aa0;
    font-weight: bold;
}

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
    margin-bottom: 1rem;
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
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    border-color: #2c5aa0;
    outline: none;
    box-shadow: 0 0 0 2px rgba(44, 90, 160, 0.1);
}

.form-group select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-color: white; 


    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236c757d%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right .7em top 50%;
    background-size: .65em auto;
}


.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    box-sizing: border-box; 
}


.form-group input:focus,
.form-group select:focus {
    border-color: #2c5aa0;
    outline: none;
    box-shadow: 0 0 0 2px rgba(44, 90, 160, 0.1);
}

.required {
    color: #dc3545;
}

.text-danger {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
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
    transform: translateY(-2px);
}

.back-payment-btn {
    background: #6c757d;
    color: white;
}

.back-payment-btn:hover {
    background: #545b62;
    transform: translateY(-2px);
}

/* Responsive design */
@media (max-width: 768px) {
    .summary-card {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .booking-progress {
        gap: 1rem;
    }
    
    .progress-step {
        font-size: 0.8rem;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
    }
}
</style>
@endsection