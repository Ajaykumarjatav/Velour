<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'Velour' }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f5f3ff; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; color:#1f2937; }
  .wrapper { max-width:560px; margin:40px auto; }
  .header { background:#7c3aed; padding:28px 32px; border-radius:16px 16px 0 0; text-align:center; }
  .header-logo { color:#fff; font-size:22px; font-weight:800; letter-spacing:-0.5px; }
  .header-logo span { color:#c4b5fd; }
  .body { background:#fff; padding:36px 32px; border:1px solid #e5e7eb; border-top:none; }
  .greeting { font-size:18px; font-weight:600; color:#111827; margin-bottom:12px; }
  .text { font-size:15px; color:#4b5563; line-height:1.6; margin-bottom:20px; }
  .btn { display:inline-block; background:#7c3aed; color:#fff !important; text-decoration:none;
         padding:13px 28px; border-radius:10px; font-size:15px; font-weight:600; margin:8px 0 20px; }
  .btn:hover { background:#6d28d9; }
  .code-box { background:#f5f3ff; border:2px dashed #c4b5fd; border-radius:12px;
              padding:20px; text-align:center; margin:20px 0; }
  .code-text { font-size:36px; font-weight:800; letter-spacing:8px; color:#7c3aed; font-family:monospace; }
  .note { font-size:13px; color:#9ca3af; line-height:1.5; margin-top:8px; }
  .divider { border:none; border-top:1px solid #f3f4f6; margin:24px 0; }
  .footer { background:#f9fafb; padding:20px 32px; border:1px solid #e5e7eb;
            border-top:none; border-radius:0 0 16px 16px; text-align:center; }
  .footer p { font-size:12px; color:#9ca3af; line-height:1.6; }
  .footer a { color:#7c3aed; text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">velour<span>.</span></div>
  </div>
  <div class="body">
    @yield('body')
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} Velour · Salon Management Platform</p>
    <p>You received this email because you have an account at <a href="{{ config('app.url') }}">velour.app</a></p>
  </div>
</div>
</body>
</html>
