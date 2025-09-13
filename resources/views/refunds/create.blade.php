@extends('layout.app')

@section('title', 'Request Refund')

@section('content')

{{-- Page styles --}}
<style>
.refunds-page{max-width:800px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{max-width:1400px;margin:100px auto 2rem;min-height:calc(100vh - 200px);margin-top:150px}

.booking-summary{background:#f8f9fa;border:1px solid #e9ecef;border-radius:15px;padding:20px;margin-bottom:2rem}
.booking-summary h3{margin-bottom:1rem;color:#111827}
.booking-info{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
.info-item{display:flex;flex-direction:column}
.info-item strong{color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px}
.info-item span{font-weight:700;color:#111827;margin-top:2px}

.refund-form{background:#fff;border:1px solid #eef0f3;border-radius:15px;padding:25px;box-shadow:0 4px 14px rgba(0,0,0,.05)}
.form-group{margin-bottom:1.5rem}
.form-group label{display:block;margin-bottom:0.5rem;font-weight:700;color:#111827}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;transition:border-color .15s ease}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#111827;box-shadow:0 0 0 3px rgba(17,24,39,.08)}
.form-group small{display:block;margin-top:4px;color:#6b7280;font-size:12px}
.form-actions{display:flex;gap:1rem;margin-top:2rem;justify-content:flex-end}

.btn{appearance:none;border:0;padding:12px 20px;border-radius:10px;font-weight:700;cursor:pointer;transition:all .15s ease;text-decoration:none;display:inline-block}
.btn-primary{background:#111827;color:#fff}
.btn-primary:hover{background:#374151;transform:translateY(-1px)}
.btn-secondary{background:#6b7280;color:#fff}
.btn-secondary:hover{background:#4b5563}

.error{color:#dc2626;font-size:12px;margin-top:4px;display:block}
.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:600}
.alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

@media (max-width: 560px){
  .booking-info{grid-template-columns:1fr}
  .form-actions{flex-direction:column}
}
</style>

<div class="refunds-page">
    <h1 class="page-title">Request Refund</h1>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    <!-- Booking Summary -->
    <div class="booking-summary">
        <h3>Booking Details</h3>
        <div class="booking-info">
            <div class="info-item">
                <strong>Reference</strong>
                <span>{{ $booking->booking_reference }}</span>
            </div>
            <div class="info-item">
                <strong>Service</strong>
                <span>{{ $booking->service->name }}</span>
            </div>
            <div class="info-item">
                <strong>Stylist</strong>
                <span>{{ $booking->stylist->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <strong>Date</strong>
                <span>{{ date('M j, Y', strtotime($booking->booking_date)) }}</span>
            </div>
            <div class="info-item">
                <strong>Time</strong>
                <span>{{ date('g:i A', strtotime($booking->booking_time)) }}</span>
            </div>
            <div class="info-item">
                <strong>Amount Paid</strong>
                <span>RM{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="info-item">
                <strong>Payment Method</strong>
                <span>{{ $payment->formatted_payment_method }}</span>
            </div>
        </div>
    </div>

    <!-- Refund Form -->
    <form method="POST" action="{{ route('refunds.store') }}" class="refund-form">
        @csrf
        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

        <div class="form-group">
            <label for="refund_type">Refund Type</label>
            <select name="refund_type" id="refund_type" required onchange="toggleRefundAmount()">
                <option value="">Select refund type</option>
                <option value="full" {{ old('refund_type') == 'full' ? 'selected' : '' }}>Full Refund</option>
                <option value="partial" {{ old('refund_type') == 'partial' ? 'selected' : '' }}>Partial Refund</option>
            </select>
            @error('refund_type')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" id="amount_group" style="display: none;">
            <label for="refund_amount">Refund Amount</label>
            <input type="number" name="refund_amount" id="refund_amount" 
                   step="0.01" min="0.01" max="{{ $payment->amount }}"
                   value="{{ old('refund_amount') }}"
                   placeholder="Enter refund amount">
            <small>Maximum refund amount: RM{{ number_format($payment->amount, 2) }}</small>
            @error('refund_amount')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="reason">Reason for Refund</label>
            <textarea name="reason" id="reason" rows="4" required 
                      placeholder="Please explain why you're requesting a refund...">{{ old('reason') }}</textarea>
            @error('reason')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('refunds.refund') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit Refund Request</button>
        </div>
    </form>
</div>

<script>
function toggleRefundAmount() {
    const refundType = document.getElementById('refund_type').value;
    const amountGroup = document.getElementById('amount_group');
    const amountInput = document.getElementById('refund_amount');
    
    if (refundType === 'full') {
        amountGroup.style.display = 'none';
        amountInput.value = {{ $payment->amount }};
        amountInput.required = false;
    } else if (refundType === 'partial') {
        amountGroup.style.display = 'block';
        amountInput.value = '';
        amountInput.required = true;
    } else {
        amountGroup.style.display = 'none';
        amountInput.value = '';
        amountInput.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleRefundAmount();
});
</script>

@endsection