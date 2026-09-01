@extends('emails.auth._layout', ['subject' => 'Booking request received'])
@section('body')
@php
  $tz = \App\Support\SalonTime::timezone($salon);
  $whenLabel = $appointment->starts_at->copy()->timezone($tz)->format('l, d M Y \a\t g:i A');
  $clientName = trim((string) ($client->first_name ?? ''));
  $greeting = $clientName !== '' ? $clientName : 'there';
  $services = $appointment->services->map(fn ($s) => $s->service_name)->filter()->implode(', ') ?: '—';
@endphp

<p class="greeting">Booking request received</p>
<p class="text">
  Hi {{ $greeting }}, we received your booking request at <strong>{{ $salon->name }}</strong>.
  The salon will review and confirm shortly — we'll email you once it's approved.
</p>

<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;padding:14px 16px;margin:0 0 20px;">
  <p style="margin:0;font-size:13px;font-weight:600;color:#92400e;">⏳ Pending confirmation</p>
</div>

<table style="width:100%;border-collapse:collapse;margin:4px 0 20px;">
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;width:38%;">Services</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $services }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Date &amp; time</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $whenLabel }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Staff</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->staff?->name ?? 'Any available' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Duration</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->duration_minutes }} minutes</td>
  </tr>
  <tr>
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Reference</td>
    <td style="padding:10px 0;font-size:14px;font-weight:700;color:#7c3aed;font-family:monospace;">{{ $appointment->reference }}</td>
  </tr>
</table>

@if($salon->address_line1 || $salon->city)
<p class="text" style="margin-bottom:8px;">
  <strong>Salon address</strong><br>
  {{ collect([$salon->address_line1, $salon->city, $salon->postcode])->filter()->implode(', ') }}
</p>
@endif

<div style="text-align:center;margin-top:24px;">
  <a href="{{ \App\Support\MailUrl::clientAppointment($salon, $appointment) }}" class="btn" style="background:#7c3aed;">View booking request</a>
</div>

<hr class="divider">
<p class="note">You don't need to do anything else for now. Times shown in salon timezone ({{ $tz }}).</p>
@endsection
