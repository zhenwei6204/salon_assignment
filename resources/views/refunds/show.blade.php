@extends('layout.app')

@section('title', 'Refund Details')

@section('content')

{{-- Page styles --}}
<style>
.refunds-page{max-width:800px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{max-width:1400px;margin:100px auto 2rem;min-height:calc(100vh - 200px);margin-top:150px}

.status-banner{padding:20px;border-radius:15px;margin-bottom:2rem;text-align:center;font-weight:700;font-size:18px}
.status-pending{background:#fff3cd;color:#856404;border:2px solid #fde68a}
.status-approved{background:#dbeafe;color:#1e40af;border:2px solid #93c5fd}
.status-processing{background:#e0e7ff;color:#3730a3;border:2px solid #c7d2fe}
.status-completed{background:#d4edda;color:#155724;border:2px solid #a7f3d0}
.status-rejected{background:#fef2f2;color:#991b1b;border:2px solid #fecaca}

.details-card{background:#fff;border:1px solid #eef0f3;border-radius:15px;padding:25px;box-shadow:0 4px 14px rgba(0,0,0,.05);margin-bottom:2rem}
.details-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem}
.detail-item{display:flex;flex-direction:column;padding:15px;background:#f8f9fa;border-radius:10px}
.detail-item strong{color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px}
.detail-item span{font-weight:700;color:#111827;font-size:16px}
.detail-item--wide{grid-column:1/-1}

.timeline{margin:2rem 0}
.timeline h3{margin-bottom:1rem;color:#111827}
.timeline-item{display:flex;gap:15px;margin-bottom:20px}
.timeline-dot{width:12px;height:12px;border-radius:50%;background:#e5e7eb;margin-top:6px;flex-shrink:0}
.timeline-dot--active{background:#10b981}
.timeline-content h4{margin:0 0 4px 0;font-size:14px;font-weight:700;color:#111827}
.timeline-content p{margin:0;color:#6b7280;font-size:13px}

.actions{display:flex;gap:1rem;justify-content:flex-end;margin-top:2rem}
.btn{appearance:none;border:0;padding:12px 20px;border-radius:10px;font-weight:700;cursor:pointer;transition:all .15s ease;text-decoration:none;display:inline-block}
.btn-primary{background:#111827;color:#fff}
.btn-primary:hover{background:#374151}
.btn-secondary{background:#6b7280;color:#fff}
.btn-secondary:hover{background:#4b5563}
.btn-danger{background:#dc2626;color:#fff}
.btn-danger:hover{background:#b91c1c}

.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:600}
.alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

@media (max-width: 560px){
  .details-grid{grid-template-columns:1fr}
  .actions{flex-direction:column}
}
</style>

<div class="refunds-page">
    <h1 class="page-title">Refund Request: {{ $refund->refund_reference }}</h1>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    <!-- Status Banner -->
    <div class="status-banner status-{{ $refund->status_badge_color }}">
        {{ $refund->formatted_status }}
    </div>

    <!-- Refund Details -->
    <div class="details-card">
        <h3 style="margin-bottom: 1.5rem;">Refund Information</h3>
        <div class="details-grid">
            <div class="detail-item">
                <strong>Refund Amount</strong>
                <span>RM{{ number_format($refund->refund_amount, 2) }}</span>
            </div>
            <div class="detail-item">
                <strong>Original Amount</strong>
                <span>RM{{ number_format($refund->original_amount, 2) }}</span>
            </div>
            <div class="detail-item">
                <strong>Refund Type</strong>
                <span>{{ ucfirst($refund->refund_type) }} Refund</span>
            </div>
            @if($refund->refund_type === 'partial')
            <div class="detail-item">
                <strong>Percentage</strong>
                <span>{{ $refund->refund_percentage }}%</span>
            </div>
            @endif
            <div class="detail-item">
                <strong>Refund Method</strong>
                <span>{{ $refund->refund_method ? ucfirst(str_replace('_', ' ', $refund->refund_method)) : 'Not specified' }}</span>
            </div>
            <div class="detail-item">
                <strong>Requested Date</strong>
                <span>{{ $refund->created_at->format('M j, Y g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Booking Details -->
    <div class="details-card">
        <h3 style="margin-bottom: 1.5rem;">Related Booking</h3>
        <div class="details-grid">
            <div class="detail-item">
                <strong>Booking Reference</strong>
                <span>{{ $refund->booking->booking_reference }}</span>
            </div>
            <div class="detail-item">
                <strong>Service</strong>
                <span>{{ $refund->booking->service->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <strong>Stylist</strong>
                <span>{{ $refund->booking->stylist->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <strong>Booking Date</strong>
                <span>{{ $refund->booking ? date('M j, Y', strtotime($refund->booking->booking_date)) : 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- Refund Reason -->
    @if($refund->reason)
    <div class="details-card">
        <h3 style="margin-bottom: 1rem;">Reason for Refund</h3>
        <div class="detail-item detail-item--wide">
            <span>{{ $refund->reason }}</span>
        </div>
    </div>
    @endif

    <!-- Admin Notes -->
    @if($refund->admin_notes)
    <div class="details-card">
        <h3 style="margin-bottom: 1rem;">Admin Notes</h3>
        <div class="detail-item detail-item--wide">
            <span>{{ $refund->admin_notes }}</span>
        </div>
    </div>
    @endif

    <!-- Timeline -->
    <div class="details-card">
        <div class="timeline">
            <h3>Refund Timeline</h3>
            
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot--active"></div>
                <div class="timeline-content">
                    <h4>Refund Requested</h4>
                    <p>{{ $refund->requested_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>

            @if($refund->approved_at)
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot--active"></div>
                <div class="timeline-content">
                    <h4>Request Approved</h4>
                    <p>{{ $refund->approved_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
            @endif

            @if($refund->processed_at)
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot--active"></div>
                <div class="timeline-content">
                    <h4>Processing Started</h4>
                    <p>{{ $refund->processed_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
            @endif

            @if($refund->completed_at)
            <div class="timeline-item">
                <div class="timeline-dot timeline-dot--active"></div>
                <div class="timeline-content">
                    <h4>Refund Completed</h4>
                    <p>{{ $refund->completed_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <a href="{{ route('refunds.refund') }}" class="btn btn-secondary">Back to Refunds</a>
        @if($refund->canBeCancelled())
            <form method="POST" action="{{ route('refunds.cancel', $refund) }}" style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to cancel this refund request?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-danger">Cancel Request</button>
            </form>
        @endif
    </div>
</div>

@endsection