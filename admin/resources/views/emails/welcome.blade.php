<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to Velour</title>
<style>
  body{font-family:Georgia,serif;background:#0a0a0a;margin:0;padding:20px;}
  .wrap{max-width:600px;margin:0 auto;background:#141414;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;}
  .header{background:linear-gradient(135deg,#1a1412,#2d1f10);padding:48px 40px;text-align:center;border-bottom:1px solid rgba(184,148,58,.3);}
  .logo{font-size:28px;color:#B8943A;letter-spacing:6px;text-transform:uppercase;}
  .body{padding:48px 40px;color:#e0d5c5;}
  h1{color:#B8943A;font-size:28px;margin:0 0 8px;font-weight:normal;}
  .step{display:flex;gap:16px;margin:20px 0;padding:20px;background:#1a1a1a;border-radius:8px;border-left:3px solid #B8943A;}
  .step-num{color:#B8943A;font-size:22px;font-weight:bold;min-width:32px;}
  .step-text h3{margin:0 0 4px;color:#e0d5c5;font-size:15px;}
  .step-text p{margin:0;color:#888;font-size:13px;}
  .btn{display:inline-block;background:#B8943A;color:#0a0a0a;padding:16px 40px;border-radius:8px;text-decoration:none;font-size:14px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;margin-top:28px;}
  .footer{padding:24px 40px;background:#0d0d0d;color:#555;font-size:12px;text-align:center;border-top:1px solid #1a1a1a;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">Velour</div>
    <p style="color:#B8943A;margin:12px 0 0;font-size:13px;letter-spacing:3px;">SALON MANAGEMENT</p>
  </div>
  <div class="body">
    <h1>Welcome, {{ $user->name }}.</h1>
    <p style="color:#888;font-size:16px;line-height:1.7;margin:0 0 32px;">
      <strong style="color:#e0d5c5;">{{ $salon->name }}</strong> is ready. Here's how to get started in three steps.
    </p>

    <div class="step">
      <div class="step-num">1</div>
      <div class="step-text">
        <h3>Add your services</h3>
        <p>Create your service menu with durations, pricing, and deposit settings.</p>
      </div>
    </div>

    <div class="step">
      <div class="step-num">2</div>
      <div class="step-text">
        <h3>Invite your team</h3>
        <p>Add staff members, assign roles, and set working schedules.</p>
      </div>
    </div>

    <div class="step">
      <div class="step-num">3</div>
      <div class="step-text">
        <h3>Go live with online booking</h3>
        <p>Share your booking link or embed the widget on your website.</p>
      </div>
    </div>

    <a href="{{ config('app.frontend_url') }}/dashboard" class="btn">Open Your Dashboard</a>

    <p style="color:#555;font-size:12px;margin-top:32px;line-height:1.8;">
      Your booking link: <a href="{{ config('app.frontend_url') }}/book/{{ $salon->slug }}" style="color:#B8943A;">
        {{ config('app.frontend_url') }}/book/{{ $salon->slug }}
      </a>
    </p>
  </div>
  <div class="footer">
    Velour Salon SaaS &bull; <a href="/cdn-cgi/l/email-protection#60131510100f12142016050c0f15124e011010" style="color:#555;"><span class="__cf_email__" data-cfemail="ed9e989d9d829f99ad9b888182989fc38c9d9d">[email&#160;protected]</span></a