<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Appointment Confirmed — {{ $salon->name }}</title>
<style>
  body { font-family: 'Georgia', serif; background: #0a0a0a; margin: 0; padding: 20px; }
  .wrap { max-width: 600px; margin: 0 auto; background: #141414; border: 1px solid #2a2a2a; border-radius: 12px; overflow: hidden; }
  .header { background: linear-gradient(135deg,#1a1412 0%,#2d1f10 100%); padding: 40px 40px 30px; border-bottom: 1px solid rgba(184,148,58,0.3); }
  .logo { font-family: 'Georgia', serif; font-size: 22px; color: #B8943A; letter-spacing: 4px; text-transform: uppercase; }
  .body { padding: 40px; color: #e0d5c5; }
  h1 { color: #B8943A; font-size: 26px; margin: 0 0 8px; font-weight: normal; letter-spacing: 1px; }
  .divider { height: 1px; background: rgba(184,148,58,0.2); margin: 24px 0; }
  .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #222; }
  .detail-label { color: #888; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
  .detail-value { color: #e0d5c5; font-size: 14px; text-align: right; }
  .btn { display: inline-block; background: #B8943A; color: #0a0a0a; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; margin-top: 28px; font-weight: bold; }
  .footer { padding: 24px 40px; background: #0d0d0d; color: #555; font-size: 12px; text-align: center; line-height: 1.8; border-top: 1px solid #1a1a1a; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">{{ $salon->name }}</div>
    <p style="color:#888;margin:8px 0 0;font-size:13px;letter-spacing:2px;">APPOINTMENT CONFIRMED</p>
  </div>
  <div class="body">
    <h1>You're all booked, {{ $client->first_name }}.</h1>
    <p style="color:#888;margin:0 0 24px;font-size:15px;">We're looking forward to seeing you. Here are your appointment details:</p>
    <div class="divider"></div>
    <div class="detail-row">
      <span class="detail-label">Service</span>
      <span class="detail-value">{{ $appointment->services->pluck('service_name')->join(', ') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Date</span>
      <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->starts_at)->format('l, j F Y') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Time</span>
      <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->starts_at)->format('g:i A') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Stylist</span>
      <span class="detail-value">{{ $appointment->staff->first_name }} {{ $appointment->staff->last_name }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Duration</span>
      <span class="detail-value">{{ $appointment->duration_minutes }} minutes</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Reference</span>
      <span class="detail-value" style="color:#B8943A;font-family:monospace;">{{ $appointment->reference }}</span>
    </div>
    <div class="divider"></div>
    <p style="color:#888;font-size:14px;line-height:1.7;">
      <strong style="color:#e0d5c5;">Address:</strong><br>
      {{ $salon->address_line1 }}, {{ $salon->city }}, {{ $salon->postcode }}
    </p>
    @if($appointment->salon->cancellation_hours)
    <p style="color:#666;font-size:12px;margin-top:20px;padding:12px;border:1px solid #222;border-radius:6px;line-height:1.6;">
      ⚠️ Please note: appointments must be cancelled at least {{ $appointment->salon->cancellation_hours }} hours in advance to avoid a cancellation fee.
    </p>
    @endif
    <a href="{{ config('app.frontend_url') }}/book/{{ $salon->slug }}/appointment/{{ $appointment->reference }}" class="btn">Manage Booking</a>
  </div>
  <div class="footer">
    {{ $salon->name }} &bull; {{ $salon->address_line1 }}, {{ $salon->city }}<br>
    <a href="{{ config('app.frontend_url') }}" style="color:#B8943A;text-decoration:none;">velour.app</a>
    &bull; <a href="/cdn-cgi/l/email-protection#3f44441f1b4c5e53505112015a525e56531f4242" style="color:#555;">{{ $salon->ema