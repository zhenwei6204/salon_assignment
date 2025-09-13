@extends('layout.app')

@section('title', 'My Refunds')

@section('content')

{{-- Page styles --}}
<style>
.refunds-page{max-width:900px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{max-width:1400px;margin:100px auto 2rem;min-height:calc(100vh - 200px);margin-top:150px}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem}
.stat-card{background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 4px 14px rgba(0,0,0,.05);text-align:center}
.stat-card h3{font-size:2rem;margin:0 0 0.5rem 0;color:#111827}
.stat-card p{color:#6b7280;margin:0}

.filters{position:sticky;top:70px;background:#fff;border:1px solid #eef0f3;border-radius:14px;padding: 25px 25px 25px;box-shadow:0 4px 16px rgba(0,0,0,.04);z-index:5;margin-bottom:14px}
.filters-row{display:grid;grid-template-columns:1.2fr .8fr .6fr .6fr auto;gap:10px;align-items:end}
.f-col{display:flex;flex-direction:column}
.f-label{font-size:12px;color:#6b7280;margin-bottom:6px}
.f-input{height:40px;border:1px solid #e5e7eb;border-radius:10px;padding:0 12px}
.f-input:focus{outline:none;border-color:#111827;box-shadow:0 0 0 3px rgba(17,24,39,.08)}
.f-actions{display:flex;gap:8px;align-items:center}
.filters-meta{padding:6px 2px 0;color:#6b7280;font-size:13px;margin-bottom:10px}

.list{display:flex;flex-direction:column;gap:12px}
.row-card{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:14px 14px 12px;box-shadow:0 4px 14px rgba(0,0,0,.05);transition:box-shadow .15s ease, transform .15s ease}
.row-card:hover{box-shadow:0 8px 22px rgba(0,0,0,.07);transform:translateY(-1px)}
.row-head{display:flex;gap:10px;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.row-title h3{margin:4px 0 4px;font-size:18px}
.muted{color:#4b5563}
.ref-chip{display:inline-block;background:#f3f4f6;color:#6b7280;border-radius:999px;padding:3px 8px;font-size:12px}

.badge{padding:6px 10px;font-size:12px;font-weight:700;border-radius:999px;white-space:nowrap;height:fit-content}
.badge--pending{background:#fff3cd;color:#856404;border:1px solid #fde68a}
.badge--completed{background:#d4edda;color:#155724;border:1px solid #a7f3d0}
.badge--rejected{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.badge--approved{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd}
.badge--processing{background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe}

.row-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 10px;margin:10px 0 6px}
.kv{display:flex;flex-direction:column;border:1px dashed #e5e7eb;border-radius:10px;padding:8px}
.kv--wide{grid-column:1/-1}
.kv dt{font-size:12px;color:#6b7280}
.kv dd{font-weight:700;margin:0}

.row-actions{display:flex;justify-content:flex-end;gap:8px}

.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:600}
.alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.btn{appearance:none;border:0;padding:10px 14px;border-radius:10px;font-weight:700;cursor:pointer;transition:transform .06s ease, box-shadow .12s ease;text-decoration:none;display:inline-block}
.btn:active{transform:translateY(1px)}
.btn-primary{background:#111827;color:#fff}
.btn-primary:hover{box-shadow:0 6px 16px rgba(17,24,39,.18)}
.btn-danger{background:#dc2626;color:#fff}
.btn-danger:hover{background:#b91c1c}
.btn-muted{background:#f3f4f6;color:#6b7280}
.btn-muted[disabled]{opacity:.7;cursor:not-allowed}
.btn-outline{background:transparent;color:#111827;border:1px solid #d1d5db}
.btn-outline:hover{background:#f9fafb}

.empty-state{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:36px 24px;text-align:center;box-shadow:0 4px 18px rgba(0,0,0,.04)}
.empty-icon{font-size:40px}
.link{color:#111827;text-decoration:underline}

@media (max-width: 880px){
  .filters-row{grid-template-columns:1fr 1fr 1fr 1fr;gap:10px}
  .f-actions{grid-column:1/-1}
  .row-grid{grid-template-columns:repeat(2,1fr)}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width: 560px){
  .filters-row{grid-template-columns:1fr 1fr}
  .row-grid{grid-template-columns:1fr}
  .stats-grid{grid-template-columns:1fr}
}
</style>

<div class="refunds-page">
    <div class="row-head">
        <h1 class="page-title">My Refunds</h1>
        <a href="{{ route('refunds.create') }}" class="btn btn-primary">Request Refund</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>{{ $stats['total_refunds'] ?? 0 }}</h3>
            <p>Total Requests</p>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['pending_refunds'] ?? 0 }}</h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3>{{ $stats['completed_refunds'] ?? 0 }}</h3>
            <p>Completed</p>
        </div>
        <div class="stat-card">
            <h3>RM{{ number_format($stats['total_refunded_amount'] ?? 0, 2) }}</h3>
            <p>Total Refunded</p>
        </div>
    </div>

    {{-- Filters --}}
    <form id="filters" class="filters" method="GET" action="{{ route('refunds.index') }}">
        <div class="filters-row">
            <div class="f-col">
                <label class="f-label">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by refund reference, booking..." class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">Status</label>
                <select name="status" class="f-input">
                    <option value="">All Status</option>
                    <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($filters['status'] ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="processing" {{ ($filters['status'] ?? '') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="f-col">
                <label class="f-label">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="f-input">
            </div>

            <div class="f-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('refunds.index') }}" class="btn btn-muted">Clear</a>
            </div>
        </div>
    </form>

    {{-- Results counter --}}
    <div class="filters-meta">
        <span>Showing {{ $refunds->firstItem() ?? 0 }}–{{ $refunds->lastItem() ?? 0 }} of {{ $refunds->total() ?? 0 }} result{{ ($refunds->total() ?? 0) === 1 ? '' : 's' }}</span>
    </div>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    {{-- Refunds List --}}
    @if($refunds->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">💸</div>
            <h2>No refund requests found</h2>
            <p>Try changing the filters or <a class="link" href="{{ route('refunds.create') }}">request a refund</a>.</p>
        </div>
    @else
        <div class="list">
            @foreach($refunds as $refund)
                <article class="row-card">
                    <header class="row-head">
                        <div class="row-main">
                            <div class="row-title">
                                <span class="ref-chip">{{ $refund->refund_reference }}</span>
                                <h3>{{ $refund->booking->service->name ?? 'Service' }}</h3>
                                <div class="muted">Booking: <strong>{{ $refund->booking->booking_reference }}</strong></div>
                            </div>
                        </div>
                        @php
                            $badgeClass = match($refund->status) {
                                'pending' => 'badge--pending',
                                'approved' => 'badge--approved', 
                                'processing' => 'badge--processing',
                                'completed' => 'badge--completed',
                                'rejected' => 'badge--rejected',
                                default => 'badge--pending'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($refund->status) }}</span>
                    </header>

                    <dl class="row-grid">
                        <div class="kv">
                            <dt>Amount</dt>
                            <dd>RM {{ number_format($refund->refund_amount, 2) }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Type</dt>
                            <dd>{{ ucfirst($refund->refund_type) }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Requested</dt>
                            <dd>{{ $refund->created_at->format('M j, Y') }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Original</dt>
                            <dd>RM {{ number_format($refund->original_amount, 2) }}</dd>
                        </div>
                        @if($refund->reason)
                            <div class="kv kv--wide">
                                <dt>Reason</dt>
                                <dd>{{ Str::limit($refund->reason, 100) }}</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Add actions if needed --}}
                    <div class="row-actions">
                        @if($refund->status === 'pending')
                            <span class="badge badge--pending">Awaiting Review</span>
                        @elseif($refund->status === 'completed')
                            <span class="badge badge--completed">Refund Processed</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div style="margin-top: 2rem;">
            {{ $refunds->appends(request()->query())->links() }}
        </div>
    @endif
</div>

{{-- Auto-submit on select/date changes --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('filters');
    if (form) {
        form.querySelectorAll('select,input[type="date"]').forEach(el => {
            el.addEventListener('change', () => form.submit());
        });
    }
});
</script>
@endsection