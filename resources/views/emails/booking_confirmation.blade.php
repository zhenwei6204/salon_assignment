@php
    use Illuminate\Support\Carbon;

    // Pre-format strings for safety across email clients
    $dateStr  = Carbon::parse($booking->booking_date)->format('l, F j, Y');
    $startStr = Carbon::parse($booking->booking_time)->format('g:i A');
    $endStr   = Carbon::parse($booking->end_time)->format('g:i A');

    // Defaults if not provided
    $mode         = $mode         ?? 'self';         // 'self' | 'other' | 'booker'
    $bookerName   = $bookerName   ?? null;           // only when 'other' or 'booker'
    $bookerEmail  = $bookerEmail  ?? null;           // only when 'other' or 'booker'
@endphp

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Booking Confirmation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    /* ===== Reset minis ===== */
    body { margin:0; padding:0; background:#f6f7fb; -webkit-text-size-adjust:100%; }
    table { border-collapse:collapse; border-spacing:0; }
    img { border:0; outline:none; text-decoration:none; display:block; }
    a { color:#0ea5e9; text-decoration:none; }
    .sr { color:#111827; }

    /* ===== Layout ===== */
    .wrap  { width:100%; padding:24px 0; background:#f6f7fb; }
    .card  { width:100%; max-width:640px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(17,24,39,.08); }

    /* Header */
    .hd     { background:#0f172a; padding:22px 26px; color:#fff; }
    .brand  { display:flex; align-items:center; gap:12px; }
    .logo   { width:32px; height:32px; border-radius:8px; background:#f59e0b; }
    .bname  { font:700 18px/1.1 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; letter-spacing:.2px; }

    /* Body */
    .bd     { padding:28px 24px; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; color:#111827; }
    h1      { margin:0 0 12px; font:700 22px/1.2 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; }
    p       { margin:0 0 14px; color:#374151; line-height:1.55; }

    /* Summary panel */
    .sum    { border:1px solid #e5e7eb; background:#fafafa; border-radius:12px; padding:16px; }
    .row    { border-bottom:1px dashed #e5e7eb; }
    .row:last-child { border-bottom:0; }
    /* two clean columns with breathing space */
    .twocol { width:100%; }
    .twocol td { width:50%; vertical-align:top; padding:10px 14px; }
    .twocol .left  { padding-right:26px; }   /* <- add spacing between SERVICE / STYLIST */
    .twocol .right { padding-left:26px; }    /* <- add spacing between DATE / TIME etc.  */

    .lbl    { font:700 11px/1.2 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; letter-spacing:.4px; color:#6b7280; text-transform:uppercase; margin-bottom:4px; }
    .val    { font:600 14px/1.3 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; color:#111827; }

    /* Buttons */
    .btns   { padding-top:8px; }
    .btn    { display:inline-block; border-radius:10px; padding:12px 18px; font:700 14px/1 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; }
    .btn.pri{ background:#f59e0b; color:#111827; margin-right:10px; }
    .btn.sec{ background:#111827; color:#fff; }

    /* Extra panel for "booked for someone else" or "booker confirmation" */
    .guest  { margin-top:22px; border:1px solid #e5e7eb; border-radius:12px; }
    .guest td { padding:18px 22px; color:#0f172a; }

    /* Footer */
    .ft     { color:#6b7280; text-align:center; font:500 12px/1.45 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; padding:20px 24px 26px; }

    /* Mobile */
    @media (max-width: 480px) {
      .twocol td { display:block; width:100% !important; padding:10px 0 !important; }
      .twocol .left, .twocol .right { padding-left:0 !important; padding-right:0 !important; }
      .btn { width:100%; text-align:center; margin:8px 0 0; }
    }
  </style>
</head>
<body class="sr">
  <div class="wrap">
    <table role="presentation" class="card">
      <!-- Header -->
      <tr>
        <td class="hd">
          <div class="brand">
            <div class="logo"></div>
            <div class="bname">Salon Good</div>
          </div>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td class="bd">
          @if ($mode === 'booker')
            <h1>Booking Confirmation for {{ $booking->customer_name }}</h1>
            <p>You have successfully booked an appointment for <strong>{{ $booking->customer_name }}</strong>.</p>
            <p>Booking Reference: <strong>#{{ $booking->booking_reference }}</strong></p>
          @else
            <h1>Thank you for your booking, {{ $booking->customer_name }}!</h1>
            <p>Your booking <strong>#{{ $booking->booking_reference }}</strong> is confirmed.</p>
          @endif

          <!-- Summary Panel -->
          <table role="presentation" class="sum" width="100%" cellpadding="0" cellspacing="0">
            <!-- Service / Stylist -->
            <tr class="row">
              <td>
                <table role="presentation" class="twocol" width="100%">
                  <tr>
                    <td class="left">
                      <div class="lbl">Service</div>
                      <div class="val">{{ $booking->service->name }}</div>
                    </td>
                    <td class="right">
                      <div class="lbl">Stylist</div>
                      <div class="val">{{ $booking->stylist->name }}</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Date / Time -->
            <tr class="row">
              <td>
                <table role="presentation" class="twocol" width="100%">
                  <tr>
                    <td class="left">
                      <div class="lbl">Date</div>
                      <div class="val">{{ $dateStr }}</div>
                    </td>
                    <td class="right">
                      <div class="lbl">Time</div>
                      <div class="val">{{ $startStr }} â€“ {{ $endStr }}</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Total / Status -->
            <tr>
              <td>
                <table role="presentation" class="twocol" width="100%">
                  <tr>
                    <td class="left">
                      <div class="lbl">Total</div>
                      <div class="val">RM {{ number_format($booking->total_price, 2) }}</div>
                    </td>
                    <td class="right">
                      <div class="lbl">Status</div>
                      <div class="val" style="text-transform:capitalize">{{ $booking->status }}</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

          {{-- If booked for someone else, show guest details --}}
          @if ($mode === 'other')
            <table role="presentation" class="guest" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <h3 style="margin:0 0 6px; font:700 16px/1.2 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;">
                    Booking Details
                  </h3>

                  @if($bookerName)
                    <p style="margin:0 0 6px; color:#334155;">
                      <strong>Booked by:</strong> {{ $bookerName }}@if($bookerEmail) ({{ $bookerEmail }}) @endif
                    </p>
                  @endif

                  <p style="margin:0 0 4px; color:#334155;"><strong>Appointment for:</strong> {{ $booking->customer_name }}</p>
                  <p style="margin:0 0 4px; color:#334155;"><strong>Contact Email:</strong> {{ $booking->customer_email ?? 'â€“' }}</p>
                  <p style="margin:0 0 4px; color:#334155;"><strong>Contact Phone:</strong> {{ $booking->customer_phone ?? 'â€“' }}</p>

                  @if($booking->special_requests)
                    <p style="margin:8px 0 0; color:#334155;"><strong>Notes:</strong> {{ $booking->special_requests }}</p>
                  @endif
                </td>
              </tr>
            </table>
          @elseif ($mode === 'booker')
            <table role="presentation" class="guest" width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <h3 style="margin:0 0 6px; font:700 16px/1.2 ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;">
                    Appointment Details
                  </h3>

                  <p style="margin:0 0 4px; color:#334155;"><strong>You booked for:</strong> {{ $booking->customer_name }}</p>
                  <p style="margin:0 0 4px; color:#334155;"><strong>Their Email:</strong> {{ $booking->customer_email ?? 'â€“' }}</p>
                  <p style="margin:0 0 4px; color:#334155;"><strong>Their Phone:</strong> {{ $booking->customer_phone ?? 'â€“' }}</p>

                  @if($booking->special_requests)
                    <p style="margin:8px 0 0; color:#334155;"><strong>Notes:</strong> {{ $booking->special_requests }}</p>
                  @endif

                  <p style="margin:12px 0 0; color:#6b7280; font-size:13px;">
                    A confirmation email has also been sent to {{ $booking->customer_name }} at their email address.
                  </p>
                </td>
              </tr>
            </table>
          @endif

          <!-- Buttons -->
          <div class="btns">
            @if ($mode === 'booker')
              <a href="{{ url('/bookings') }}" class="btn btn pri">View All Bookings</a>
              <a href="{{ url('/') }}" class="btn btn sec">Book Another</a>
            @else
              <a href="{{ url('/bookings') }}" class="btn btn pri">View Booking</a>
              <a href="{{ url('/') }}" class="btn btn sec">Reschedule</a>
            @endif
          </div>

          @if ($mode === 'booker')
            <p style="margin-top:18px;">This is your booking confirmation. {{ $booking->customer_name }} will receive their own confirmation email.</p>
          @else
            <p style="margin-top:18px;">If you have any questions, just reply to this email.</p>
          @endif
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td class="ft">
          Â© {{ date('Y') }} Salon Good â€“ 123 Example Street, Kuala Lumpur<br>
          You're receiving this because you made a booking at Salon Good.
        </td>
      </tr>
    </table>
  </div>
</body>
</html>