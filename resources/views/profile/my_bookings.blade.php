@extends('layout.app')

@section('title', 'My Bookings')

@section('content')

{{-- Page styles --}}
<style>
.bookings-page{max-width:900px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{    max-width: 1400px;
    margin: 100px auto 2rem;
    min-height: calc(100vh - 200px);
    margin-top: 150px;}
.filters{position:sticky;top:70px;background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:14px 14px 8px;box-shadow:0 4px 16px rgba(0,0,0,.04);z-index:5;margin-bottom:14px}
.filters-row{display:grid;grid-template-columns:1.2fr .8fr .6fr .6fr auto;gap:10px;align-items:end}
.f-col{display:flex;flex-direction:column}
.f-label{font-size:12px;color:#6b7280;margin-bottom:6px}
.f-input{height:40px;border:1px solid #e5e7eb;border-radius:10px;padding:0 12px}
.f-actions{display:flex;gap:8px;align-items:center}
.filters-meta{padding:6px 2px 0;color:#6b7280;font-size:13px}

.list{display:flex;flex-direction:column;gap:12px}
.row-card{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:30px 30px 30px;;box-shadow:0 4px 14px rgba(0,0,0,.05)}
.row-head{display:flex;gap:10px;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.row-title h3{margin:4px 0 4px;font-size:18px}
.muted{color:#4b5563}
.ref-chip{display:inline-block;background:#f3f4f6;color:#6b7280;border-radius:999px;padding:3px 8px;font-size:12px}

.badge{padding:6px 10px;font-size:12px;font-weight:700;border-radius:999px;white-space:nowrap;height:fit-content}
.badge--booked{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.badge--cancelled{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.row-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 10px;margin:10px 0 6px}
.kv{display:flex;flex-direction:column;border:1px dashed #787e87;border-radius:10px;padding:8px}
.kv--wide{grid-column:1/-1}
.kv dt{font-size:12px;color:#6b7280}
.kv dd{font-weight:700}@extends('layout.app')

@section('title', 'My Bookings')

@section('content')

{{-- Page styles --}}
<style>
.bookings-page{max-width:900px;margin:60px auto;padding:0 18px}
.page-title{font-size:clamp(24px,3vw,32px);font-weight:800;margin-bottom:14px}
.main-content{max-width:1400px;margin:100px auto 2rem;min-height:calc(100vh - 200px);margin-top:150px}
.filters{position:sticky;top:70px;background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:14px 14px 8px;box-shadow:0 4px 16px rgba(0,0,0,.04);z-index:5;margin-bottom:14px}
.filters-row{display:grid;grid-template-columns:1.2fr .8fr .6fr .6fr auto;gap:10px;align-items:end}
.f-col{display:flex;flex-direction:column}
.f-label{font-size:12px;color:#6b7280;margin-bottom:6px}
.f-input{height:40px;border:1px solid #e5e7eb;border-radius:10px;padding:0 12px}
.f-actions{display:flex;gap:8px;align-items:center}
.filters-meta{padding:6px 2px 0;color:#6b7280;font-size:13px;margin-bottom:10px}
/* Safety: if filters-meta ever sits on top of inputs, ignore clicks */
.filters-meta{pointer-events:none}

.list{display:flex;flex-direction:column;gap:12px}
.row-card{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:14px 14px 12px;box-shadow:0 4px 14px rgba(0,0,0,.05)}
.row-head{display:flex;gap:10px;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.row-title h3{margin:4px 0 4px;font-size:18px}
.muted{color:#4b5563}
.ref-chip{display:inline-block;background:#f3f4f6;color:#6b7280;border-radius:999px;padding:3px 8px;font-size:12px}

.badge{padding:6px 10px;font-size:12px;font-weight:700;border-radius:999px;white-space:nowrap;height:fit-content}
.badge--booked{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.badge--cancelled{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.row-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px 10px;margin:10px 0 6px}
.kv{display:flex;flex-direction:column;border:1px dashed #787e87;border-radius:10px;padding:8px}
.kv--wide{grid-column:1/-1}
.kv dt{font-size:12px;color:#6b7280}
.kv dd{font-weight:700}

.row-actions{display:flex;justify-content:flex-end}

.alert{padding:12px 14px;border-radius:10px;margin-bottom:12px;font-weight:600}
.alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.btn{appearance:none;border:0;padding:10px 14px;border-radius:10px;font-weight:700;cursor:pointer}
.btn-primary{background:#111827;color:#fff}
.btn-danger{background:#dc2626;color:#fff}
.btn-danger:hover{background:#b91c1c}
.btn-muted{background:#f3f4f6;color:#6b7280}

.empty-state{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:36px 24px;text-align:center;box-shadow:0 4px 18px rgba(0,0,0,.04)}
.empty-icon{font-size:40px}
.link{color:#111827;text-decoration:underline}

.pagination-wrap{margin-top:14px}

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
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search by reference, service, stylist…" class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">Status</label>
                <select name="status" class="f-input">
                    <option value="">All</option>
                    <option value="booked" {{ $filters['status']==='booked' ? 'selected' : '' }}>Booked</option>
                    <option value="completed" {{ $filters['status']==='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $filters['status']==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="f-col">
                <label class="f-label">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="f-input">
            </div>

            <div class="f-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('bookings.index') }}" class="btn btn-muted">Clear</a>
            </div>
        </div>
    </form>

    {{-- Results counter outside the form --}}
    <div class="filters-meta">
        <span>{{ $bookings->total() }} result{{ $bookings->total() === 1 ? '' : 's' }}</span>
    </div>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    {{-- Empty state --}}
    @if($bookings->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h2>No bookings found</h2>
            <p>Try changing the filters or <a class="link" href="{{ route('categories.index') }}">book a service</a>.</p>
        </div>
    @else
        {{-- Straight single column list --}}
        <div class="list">
            @foreach($bookings as $b)
                @php
                    $start = \Carbon\Carbon::parse($b->booking_date, config('app.timezone'))
                                ->setTimeFromTimeString($b->booking_time);
                    $end   = (clone $start)->setTimeFromTimeString($b->end_time ?? $b->booking_time);
                    $isCancelled = strtolower($b->status) === 'cancelled';
                    $canCancel   = !$isCancelled && now(config('app.timezone'))->lt($start);
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
                        <span class="badge {{ $isCancelled ? 'badge--cancelled' : 'badge--booked' }}">
                            {{ ucfirst($b->status) }}
                        </span>
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
                        @if($b->end_time)
                        <div class="kv">
                            <dt>Ends</dt>
                            <dd>{{ $end->format('g:i A') }}</dd>
                        </div>
                        @endif
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
                            @else
                                <button class="btn btn-muted" disabled>Not Cancellable</button>
                            @endif
                        @endif
                    </footer>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            {{ $bookings->links() }}
        </div>
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
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search by reference, service, stylist…" class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">Status</label>
                <select name="status" class="f-input">
                    <option value="">All</option>
                    <option value="booked" {{ $filters['status']==='booked' ? 'selected' : '' }}>Booked</option>
                    <option value="completed" {{ $filters['status']==='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $filters['status']==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="f-col">
                <label class="f-label">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="f-input">
            </div>

            <div class="f-col">
                <label class="f-label">To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="f-input">
            </div>

            <div class="f-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('bookings.index') }}" class="btn btn-muted">Clear</a>
            </div>
        </div>
        <div class="filters-meta">
            <span>{{ $bookings->total() }} result{{ $bookings->total() === 1 ? '' : 's' }}</span>
        </div>
    </form>

    {{-- Flash messages --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-error">{{ session('error') }}</div>   @endif

    {{-- Empty state --}}
    @if($bookings->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h2>No bookings found</h2>
            <p>Try changing the filters or <a class="link" href="{{ route('categories.index') }}">book a service</a>.</p>
        </div>
    @else
        {{-- Straight single column list --}}
        <div class="list">
            @foreach($bookings as $b)
                @php
                    $start = \Carbon\Carbon::parse($b->booking_date, config('app.timezone'))
                                ->setTimeFromTimeString($b->booking_time);
                    $end   = (clone $start)->setTimeFromTimeString($b->end_time ?? $b->booking_time);
                    $isCancelled = strtolower($b->status) === 'cancelled';
                    $canCancel   = !$isCancelled && now(config('app.timezone'))->lt($start);
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
                        <span class="badge {{ $isCancelled ? 'badge--cancelled' : 'badge--booked' }}">
                            {{ ucfirst($b->status) }}
                        </span>
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
                        @if($b->end_time)
                        <div class="kv">
                            <dt>Ends</dt>
                            <dd>{{ $end->format('g:i A') }}</dd>
                        </div>
                        @endif
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
                            @else
                                <button class="btn btn-muted" disabled>Not Cancellable</button>
                            @endif
                        @endif
                    </footer>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            {{ $bookings->links() }}
        </div>
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
