<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Reminder — {{ $salon->name }}</title>
<style>
  body{font-family:Georgia,serif;background:#0a0a0a;margin:0;padding:20px;}
  .wrap{max-width:600px;margin:0 auto;background:#141414;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;}
  .header{background:linear-gradient(135deg,#1a1412,#2d1f10);padding:40px;border-bottom:1px solid rgba(184,148,58,.3);}
  .logo{font-size:22px;color:#B8943A;letter-spacing:4px;text-transform:uppercase;}
  .body{padding:40px;color:#e0d5c5;}
  .btn{display:inline-block;background:#B8943A;color:#0a0a0a;padding:14px 32px;border-radius:8px;text-decoration:none;font-size:14px;letter-spacing:2px;text-transform:uppercase;margin-top:24px;font-weight:bold;}
  .footer{padding:24px 40px;background:#0d0d0d;color:#555;font-size:12px;text-align:center;border-top:1px solid #1a1a1a;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">{{ $salon->name }}</div>
    <p style="color:#888;margin:8px 0 0;font-size:13px;letter-spacing:2px;">APPOINTMENT REMINDER</p>
  </div>
  <div class="body">
    <h1 style="color:#B8943A;font-size:24px;margin:0 0 16px;font-weight:normal;">See you tomorrow, {{ $client->first_name }}.</h1>
    <p style="color:#888;font-size:15px;line-height:1.7;">This is a friendly reminder about your upcoming appointment.</p>
    <p style="margin:24px 0 4px;color:#e0d5c5;">
      <strong>{{ \Carbon\Carbon::parse($appointment->starts_at)->format('l, j F') }}</strong>
      at <strong>{{ \Carbon\Carbon::parse($appointment->starts_at)->format('g:i A') }}</strong>
      with <strong>{{ $appointment->staff->first_name }}</strong>
    </p>
    <p style="color:#888;font-size:14px;">{{ $appointment->services->pluck('service_name')->join(', ') }}</p>
    <a href="{{ config('app.frontend_url') }}/book/{{ $salon->slug }}/appointment/{{ $appointment->reference }}" class="btn">View or Manage</a>
  </div>
  <div class="footer">{{ $salon->name }} &bull; {{ $salon->address_line1 }}, {{ $salon->city }}</div>
</div>
</body>
</html>
