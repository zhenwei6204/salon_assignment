<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @php
        $appName = config('app.name');
        $brand = ($appName && $appName !== 'Laravel') ? $appName : 'Salon Good';
    @endphp
    <title>Booking Cancelled - {{ $brand }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;">
    <tr>
        <td align="center" style="padding:24px 12px;">

            <!-- Card -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:720px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.06);">

                <!-- Header -->
                <tr>
                    <td style="background:#0f172a;padding:18px 24px;">
                        <table role="presentation" width="100%">
                            <tr>
                                <td style="font-weight:800;color:#ffffff;font-size:18px;line-height:1.2;">
                                        {{ $brand }}
                                    <div style="color:#9ca3af;font-size:12px;margin-top:2px;font-weight:400;">
                                        Booking cancelled
                                    </div>
                                </td>
                                <td align="right" style="vertical-align:middle;">
                                    <span style="display:inline-block;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:999px;padding:6px 12px;font-weight:700;font-size:12px;">
                                        Cancelled
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Title + Intro -->
                <tr>
                    <td style="padding:28px 28px 10px;">
                        <h1 style="margin:0 0 6px;font-size:22px;line-height:1.35;font-weight:800;color:#111827;">
                            Your booking has been cancelled
                        </h1>
                        <p style="margin:0;color:#6b7280;font-size:14px;line-height:1.7;">
                            Hello {{ $booking->customer_name ?? ($booking->user->name ?? 'there') }},<br>
                            Your booking <strong style="color:#111827;">#{{ $booking->booking_reference }}</strong> has been
                            <strong style="color:#991b1b;">cancelled</strong>.
                        </p>
                    </td>
                </tr>

                @php
                    $tz = config('app.timezone');

                    $ymd = $booking->booking_date instanceof \Carbon\Carbon
                        ? $booking->booking_date->format('Y-m-d')
                        : \Carbon\Carbon::parse((string)$booking->booking_date, $tz)->format('Y-m-d');

                    $startTime = $booking->booking_time instanceof \Carbon\Carbon
                        ? $booking->booking_time->format('H:i:s')
                        : date('H:i:s', strtotime((string)$booking->booking_time));

                    $endTime = $booking->end_time instanceof \Carbon\Carbon
                        ? $booking->end_time->format('H:i:s')
                        : date('H:i:s', strtotime((string)$booking->end_time));

                    $start = \Carbon\Carbon::parse("$ymd $startTime", $tz);
                    $end   = \Carbon\Carbon::parse("$ymd $endTime",   $tz);
                @endphp

                <!-- Details -->
                <tr>
                    <td style="padding:16px 28px 8px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">

                            <!-- Service | Stylist -->
                            <tr>
                                <td style="width:50%;padding:14px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">SERVICE</div>
                                    <div style="font-weight:700;color:#111827;font-size:14px;">
                                        {{ $booking->service->name ?? 'Service' }}
                                    </div>
                                </td>
                                <td style="width:50%;padding:14px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;border-left:1px solid #e5e7eb;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">STYLIST</div>
                                    <div style="font-weight:700;color:#111827;font-size:14px;">
                                        {{ $booking->stylist->name ?? 'Stylist' }}
                                    </div>
                                </td>
                            </tr>

                            <!-- Date | Time -->
                            <tr>
                                <td style="width:50%;padding:14px 16px;border-bottom:1px solid #e5e7eb;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">DATE</div>
                                    <div style="font-weight:700;color:#111827;font-size:14px;">
                                        {{ \Carbon\Carbon::parse($ymd, $tz)->isoFormat('dddd, MMMM D, YYYY') }}
                                    </div>
                                </td>
                                <td style="width:50%;padding:14px 16px;border-bottom:1px solid #e5e7eb;border-left:1px solid #e5e7eb;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">TIME</div>
                                    <div style="font-weight:700;color:#111827;font-size:14px;">
                                        {{ $start->format('g:i A') }} – {{ $end->format('g:i A') }}
                                    </div>
                                </td>
                            </tr>

                            <!-- Total | Status -->
                            <tr>
                                <td style="width:50%;padding:14px 16px;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">TOTAL</div>
                                    <div style="font-weight:700;color:#111827;font-size:14px;">
                                        RM {{ number_format($booking->total_price ?? ($booking->service->price ?? 0), 2) }}
                                    </div>
                                </td>
                                <td style="width:50%;padding:14px 16px;border-left:1px solid #e5e7eb;">
                                    <div style="font-size:11px;letter-spacing:.08em;color:#6b7280;font-weight:800;margin-bottom:2px;">STATUS</div>
                                    <div style="font-weight:700;color:#991b1b;font-size:14px;">
                                        Cancelled
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- CTA -->
                <tr>
                    <td style="padding:16px 28px 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right:12px;">
                                    <a href="{{ route('services.index') }}"
                                       style="display:inline-block;background:#f59e0b;color:#111827;text-decoration:none;font-weight:800;font-size:14px;padding:11px 18px;border-radius:10px;">
                                        Book Another
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('bookings.index') }}"
                                       style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;font-weight:800;font-size:14px;padding:11px 18px;border-radius:10px;">
                                        View My Bookings
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="border-top:1px solid #eef0f3;padding:18px 28px 26px;color:#6b7280;font-size:12px;line-height:1.7;border-bottom-left-radius:12px;border-bottom-right-radius:12px;">
                        Thanks,<br>{{ config('app.name', 'Salon Good') }}<br><br>
                        © {{ now()->year }} {{ config('app.name', 'Salon Good') }} — 123 Example Street, Kuala Lumpur<br>
                        You’re receiving this because you made a booking at {{ config('app.name', 'Salon Good') }}.
                    </td>
                </tr>

            </table>
            <!-- /Card -->

        </td>
    </tr>
</table>
</body>
</html>
