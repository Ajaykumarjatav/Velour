<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $subject ?? 'Message from EasyGrox Support' }}</title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#0a0a0a;margin:0;padding:20px;}
  .wrap{max-width:600px;margin:0 auto;background:#141414;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;}
  .header{padding:28px 32px;border-bottom:1px solid #2a2a2a;}
  .logo{font-size:18px;color:#B8943A;letter-spacing:3px;text-transform:uppercase;font-weight:700;}
  .body{padding:32px;color:#e0d5c5;font-size:15px;line-height:1.7;}
  .body p{margin:0 0 14px;}
  .footer{padding:20px 32px;background:#0d0d0d;color:#666;font-size:12px;border-top:1px solid #1a1a1a;text-align:center;}
  .footer a{color:#888;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">EasyGrox Support</div>
  </div>
  <div class="body">
    <p>Hi {{ $tenant->name }},</p>
    {!! nl2br(e($body)) !!}
    <p style="margin-top:28px;color:#888;">— EasyGrox Support</p>
  </div>
  <div class="footer">
    {!! \App\Support\SupportContact::helpLineHtml() !!}
  </div>
</div>
</body>
</html>
