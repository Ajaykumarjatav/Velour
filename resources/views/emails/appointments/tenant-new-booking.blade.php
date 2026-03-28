@extends('emails.auth._layout', ['subject' => 'New booking received'])
@section('body')
<p class="greeting" style="color:#7c3aed">📅 New Booking</p>
<p class="text">Hi {{ $salon->name }}, you have a new appointment booked via your online booking page.</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;">
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;width:40%;">Client</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $appointment->client->first_name }} {{ $appointment->client->last_name }}</td>
  </tr>
  @if($appointment->client->phone)
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Phone</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->client->phone }}</td>
  </tr>
  @endif
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Service</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">
      {{ $appointment->services->map(fn($s) => $s->service?->name)->filter()->implode(', ') ?: '—' }}
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Staff</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->staff?->name ?? 'Any available' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Date & Time</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $appointment->starts_at->format('l, d M Y \a\t g:ia') }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Duration</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->duration_minutes }} minutes</td>
  </tr>
  <tr>
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Total</td>
    <td style="padding:10px 0;font-size:16px;font-weight:700;color:#7c3aed;">{{ \App\Helpers\CurrencyHelper::format((float)$appointment->total_price, $salon->currency ?? 'GBP') }}</td>
  </tr>
</table>

@if($appointment->client_notes)
<p class="text"><strong>Client notes:</strong> {{ $appointment->client_notes }}</p>
@endif

<div style="text-align:center;margin-top:24px;">
  <a href="{{ url('/appointments/' . $appointment->id) }}" class="btn" style="background:#7c3aed">View Appointment →</a>
</div>

<hr class="divider">
<p class="note">This notification was sent because a customer booked via your online booking page. Ref: {{ $appointment->reference }}</p>
@endsection
