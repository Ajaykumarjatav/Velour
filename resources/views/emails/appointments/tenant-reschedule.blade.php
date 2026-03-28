@extends('emails.auth._layout', ['subject' => 'Booking rescheduled'])
@section('body')
<p class="greeting" style="color:#f59e0b">🔄 Booking Rescheduled</p>
<p class="text">Hi {{ $salon->name }}, an appointment has been rescheduled to a new time.</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;">
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;width:40%;">Client</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $appointment->client->first_name }} {{ $appointment->client->last_name }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Service</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">
      {{ $appointment->services->map(fn($s) => $s->service?->name)->filter()->implode(', ') ?: '—' }}
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Staff</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $appointment->staff?->name ?? '—' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Original Date</td>
    <td style="padding:10px 0;font-size:14px;color:#dc2626;text-decoration:line-through;">{{ $originalStartsAt->format('l, d M Y \a\t g:ia') }}</td>
  </tr>
  <tr>
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">New Date</td>
    <td style="padding:10px 0;font-size:14px;font-weight:700;color:#059669;">{{ $appointment->starts_at->format('l, d M Y \a\t g:ia') }}</td>
  </tr>
</table>

<div style="text-align:center;margin-top:24px;">
  <a href="{{ url('/appointments/' . $appointment->id) }}" class="btn" style="background:#f59e0b">View Appointment →</a>
</div>

<hr class="divider">
<p class="note">Ref: {{ $appointment->reference }}</p>
@endsection
