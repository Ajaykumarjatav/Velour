<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Request</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#111827;">
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">
          <tr>
            <td style="padding:24px;">
              <h2 style="margin:0 0 12px;font-size:20px;line-height:1.3;">Share your experience with {{ $salonName }}</h2>
              <p style="margin:0 0 12px;font-size:14px;line-height:1.6;">
                Hi {{ $clientName ?: 'there' }},
              </p>
              <p style="margin:0 0 18px;font-size:14px;line-height:1.6;">
                We would love to hear your feedback. Please take a moment to leave a review using the link below.
              </p>
              <p style="margin:0 0 24px;">
                <a href="{{ $reviewUrl }}" style="display:inline-block;background:#7c3aed;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:700;">
                  Leave a Review
                </a>
              </p>
              <p style="margin:0 0 8px;font-size:12px;line-height:1.5;color:#6b7280;">
                If the button does not work, copy this URL into your browser:
              </p>
              <p style="margin:0;font-size:12px;line-height:1.5;color:#4b5563;word-break:break-all;">
                {{ $reviewUrl }}
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

