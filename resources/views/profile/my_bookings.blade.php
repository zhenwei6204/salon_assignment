@extends('layout.app')

@section('title', 'My Bookings')

@section('content')

{{-- Page styles --}}
<style>
.bookings-page{max-width:900px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{max-width:1400px;margin:100px auto 2rem;min-height:calc(100vh - 200px);margin-top:150px}
.filters{position:sticky;top:70px;background:#fff;border:1px solid #eef0f3;border-radius:14px;padding: 25px 25px 25px;box-shadow:0 4px 16px rgba(0,0,0,.04);z-index:5;margin-bottom:14px}
.filters-row{display:grid;grid-template-columns:1.2fr .8fr .6fr .6fr auto;gap:10px;align-items:end}
.f-col{display:flex;flex-direction:column}
.f-label{font-size:12px;color:#6b7280;margin-bottom:6px}
.f-input{height:40px;border:1px solid #e5e7eb;border-radius:10px;padding:0 12px}
.f-input:focus{outline:none;border-color:#111827;box-shadow:0 0 0 3px rgba(17,24,39,.08)}
.f-actions{display:flex;gap:8px;align-items:center}
.filters-meta{padding:6px 2px 0;color:#6b7280;font-size:13px;margin-bottom:10px}
.filters-meta{pointer-events:none}

.list{display:flex;flex-direction:column;gap:12px}
.row-card{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:14px 14px 12px;box-shadow:0 4px 14px rgba(0,0,0,.05);transition:box-shadow .15s ease, transform .15s ease}
.row-card:hover{box-shadow:0 8px 22px rgba(0,0,0,.07);transform:translateY(-1px)}
.row-head{display:flex;gap:10px;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.row-title h3{margin:4px 0 4px;font-size:18px}
.muted{color:#4b5563}
.ref-chip{display:inline-block;background:#f3f4f6;color:#6b7280;border-radius:999px;padding:3px 8px;font-size:12px}

.badge{padding:6px 10px;font-size:12px;font-weight:700;border-radius:999px;white-space:nowrap;height:fit-content}
.badge--booked{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.badge--cancelled{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.badge--completed{background:#eff6ff;color:#1e3a8a;border:1px solid #bfdbfe}

.row-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 10px;margin:10px 0 6px}
.kv{display:flex;flex-direction:column;border:1px dashed #e5e7eb;border-radius:10px;padding:8px}
.kv--wide{grid-column:1/-1}
.kv dt{font-size:12px;color:#6b7280}
.kv dd{font-weight:700}

.row-actions{display:flex;justify-content:flex-end}

.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:600}
.alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.btn{appearance:none;border:0;padding:10px 14px; margin-top: 10px; border-radius:10px;font-weight:700;cursor:pointer;transition:transform .06s ease, box-shadow .12s ease}
.btn:active{transform:translateY(1px)}
.btn-primary{background:#111827;color:#fff}
.btn-primary:hover{box-shadow:0 6px 16px rgba(17,24,39,.18)}
.btn-danger{background:#dc2626;color:#fff}
.btn-danger:hover{background:#b91c1c}
.btn-muted{background:#f3f4f6;color:#6b7280}
.btn-muted[disabled]{opacity:.7;cursor:not-allowed}

.empty-state{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:36px 24px;text-align:center;box-shadow:0 4px 18px rgba(0,0,0,.04)}
.empty-icon{font-size:40px}
.link{color:#111827;text-decoration:underline}

.pagination-wrap{margin-top:14px;display:flex;flex-direction:column;gap:8px}
.page-legend{color:#6b7280;font-size:13px}
.pager{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.pager a,.pager span{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;font-weight:700;text-decoration:none;color:#111827}
.pager a:hover{box-shadow:0 4px 12px rgba(0,0,0,.06)}
.pager .is-current{background:#111827;color:#fff;border-color:#111827}
.pager .is-disabled{opacity:.45;pointer-events:none}
.pager .gap{border-style:dashed;color:#6b7280;min-width:28px}

@media (max-width: 880px){
  .filters-row{grid-template-columns:1fr 1fr 1fr 1fr;gap:10px}
  .f-actions{grid-column:1/-1}
  .row-grid{grid-template-columns:repeat(2,1fr)}
}
@media (max-width: 560px){
  .filters-row{grid-template-columns:1fr 1fr}
  .row-grid{grid-template-columns:1fr}
}
</style>

<div class="bookings-page">
    <h1 class="page-title">My Bookings</h1>

    {{-- Filters --}}
    <form id="filters" class="filters" method="GET" action="{{ route('bookings.index') }}">
        <div class="filters-row">
            <div class="f-col">
                <label class="f-label">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search by reference, service, stylist…" class="f-input" aria-label="Search bookings">
            </div>

            <div class="f-col">
                <label class="f-label">Status</label>
                <select name="status" class="f-input" aria-label="Filter by status">
                    <option value="">All</option>
                    <option value="booked" {{ $filters['status']==='booked' ? 'selected' : '' }}>Booked</option>
                    <option value="completed" {{ $filters['status']==='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $filters['status']==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="f-col">
                <label class="f-label">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="f-input" aria-label="From date">
            </div>

            <div class="f-col">
                <label class="f-label">To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="f-input" aria-label="To date">
            </div>

            <div class="f-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('bookings.index') }}" class="btn btn-muted">Clear</a>
            </div>
        </div>
    </form>

    {{-- Results counter --}}
    <div class="filters-meta">
        @php
            $first = $bookings->firstItem() ?? 0;
            $last  = $bookings->lastItem() ?? 0;
            $total = $bookings->total();
        @endphp
        <span>
            Showing {{ $first }}–{{ $last }} of {{ $total }} result{{ $total === 1 ? '' : 's' }}
        </span>
    </div>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    {{-- Empty state --}}
    @if($bookings->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">🔭</div>
            <h2>No bookings found</h2>
            <p>Try changing the filters or <a class="link" href="{{ route('categories.index') }}">book a service</a>.</p>
        </div>
    @else
        {{-- Straight single column list --}}
        <div class="list">
            @foreach($bookings as $b)
                @php
                    // Date/time handling
                    $bookingDate = \Carbon\Carbon::parse($b->booking_date, config('app.timezone'));
                    $bookingTime = \Carbon\Carbon::parse($b->booking_time, config('app.timezone'));
                    $endTime = \Carbon\Carbon::parse($b->end_time, config('app.timezone'));

                    $start = $bookingDate->copy()->setTime($bookingTime->hour, $bookingTime->minute, $bookingTime->second);
                    $end   = $bookingDate->copy()->setTime($endTime->hour, $endTime->minute, $endTime->second);

                    $status = strtolower($b->status);
                    $isCancelled = $status === 'cancelled';
                    $isCompleted = $status === 'completed';

                    $now = now(config('app.timezone'));
                    $canCancel = !$isCancelled && !$isCompleted && $start->gt($now->copy()->addHours(2));
                @endphp

                <article class="row-card">
                    <header class="row-head">
                        <div class="row-main">
                            <div class="row-title">
                                <span class="ref-chip">Ref: {{ $b->booking_reference }}</span>
                                <h3>{{ $b->service->name ?? 'Service' }}</h3>
                                <div class="muted">with <strong>{{ $b->stylist->name ?? 'Stylist' }}</strong></div>
                            </div>
                        </div>
                        @php
                            $badgeClass = $isCancelled ? 'badge--cancelled' : ($isCompleted ? 'badge--completed' : 'badge--booked');
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($b->status) }}</span>
                    </header>

                    <dl class="row-grid">
                        <div class="kv">
                            <dt>Date</dt>
                            <dd>{{ $start->format('F j, Y') }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Time</dt>
                            <dd>{{ $start->format('g:i A') }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Ends</dt>
                            <dd>{{ $end->format('g:i A') }}</dd>
                        </div>
                        <div class="kv">
                            <dt>Price</dt>
                            <dd>RM {{ number_format($b->total_price, 2) }}</dd>
                        </div>
                        @if($b->special_requests)
                        <div class="kv kv--wide">
                            <dt>Notes</dt>
                            <dd>{{ $b->special_requests }}</dd>
                        </div>
                        @endif
                    </dl>

                    <footer class="row-actions">
                        @if($canCancel)
                            <form method="POST" action="{{ route('booking.cancel', $b) }}" 
                                  onsubmit="return confirm('Cancel this booking?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger">Cancel Booking</button>
                            </form>
                        @else
                            @if($isCancelled)
                                <button class="btn btn-muted" disabled>Already Cancelled</button>
                            @elseif($isCompleted)
                                <button class="btn btn-muted" disabled>Completed</button>
                            @else
                                <button class="btn btn-muted" disabled>Cannot Cancel (too close to appointment)</button>
                            @endif
                        @endif
                    </footer>
                </article>
            @endforeach
        </div>

        {{-- Pagination (numeric + next/prev + legend). Keeps filters in the querystring. --}}
        @php
            $current = $bookings->currentPage();
            $last    = $bookings->lastPage();
            $hasPrev = $current > 1;
            $hasNext = $current < $last;
            $rangeStart = max(1, $current - 2);
            $rangeEnd   = min($last, $current + 2);

            // Build URLs that preserve existing filters
            $q = request()->query();
            unset($q['page']);
            $url = fn($page) => route('bookings.index', array_merge($q, ['page' => $page]));
        @endphp

        @if($last > 1)
        <div class="pagination-wrap" role="navigation" aria-label="Pagination Navigation">
            <div class="page-legend">
                Page {{ $current }} of {{ $last }} • Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} of {{ $bookings->total() }}
            </div>

            <div class="pager">
                {{-- First & Prev --}}
                <a href="{{ $hasPrev ? $url(1) : '#' }}" class="{{ $hasPrev ? '' : 'is-disabled' }}" aria-label="First page">«</a>
                <a href="{{ $hasPrev ? $url($current - 1) : '#' }}" class="{{ $hasPrev ? '' : 'is-disabled' }}" aria-label="Previous page">Prev</a>

                {{-- Leading gap --}}
                @if($rangeStart > 1)
                    <a href="{{ $url(1) }}">1</a>
                    @if($rangeStart > 2)
                        <span class="gap" aria-hidden="true">…</span>
                    @endif
                @endif

                {{-- Page numbers --}}
                @for($i = $rangeStart; $i <= $rangeEnd; $i++)
                    @if($i === $current)
                        <span class="is-current" aria-current="page">{{ $i }}</span>
                    @else
                        <a href="{{ $url($i) }}">{{ $i }}</a>
                    @endif
                @endfor

                {{-- Trailing gap --}}
                @if($rangeEnd < $last)
                    @if($rangeEnd < $last - 1)
                        <span class="gap" aria-hidden="true">…</span>
                    @endif
                    <a href="{{ $url($last) }}">{{ $last }}</a>
                @endif

                {{-- Next & Last --}}
                <a href="{{ $hasNext ? $url($current + 1) : '#' }}" class="{{ $hasNext ? '' : 'is-disabled' }}" aria-label="Next page">Next</a>
                <a href="{{ $hasNext ? $url($last) : '#' }}" class="{{ $hasNext ? '' : 'is-disabled' }}" aria-label="Last page">»</a>
            </div>
        </div>
        @endif
    @endif
</div>

{{-- Auto-submit on select/date changes --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('filters');
    form.querySelectorAll('select,input[type="date"]').forEach(el => {
        el.addEventListener('change', () => form.submit());
    });
});
</script>
@endsection
