@extends('emails.auth._layout', ['subject' => $event === 'new_store' ? 'New store created' : 'New user registered'])
@section('body')
<p class="greeting">{{ $event === 'new_store' ? 'New store created' : 'New user registered' }}</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;">
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;width:40%;">Event</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">
      {{ $event === 'new_store' ? 'New store / branch' : 'New user signup' }}
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Owner name</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $user->name }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Owner email</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">
      <a href="mailto:{{ $user->email }}" style="color:#7c3aed;">{{ $user->email }}</a>
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Store name</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $salon->name }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Slug</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $salon->slug }}</td>
  </tr>
  <tr>
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Environment</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ config('app.env') }} · {{ rtrim((string) config('app.url'), '/') }}</td>
  </tr>
</table>

<div style="text-align:center;margin-top:24px;">
  <a href="{{ \App\Support\MailUrl::dashboard($salon, $user) }}" class="btn">Open salon dashboard →</a>
</div>

<hr class="divider">
<p class="note">Internal EasyGrox ops alert. Booking link: {{ \App\Support\MailUrl::booking($salon) }}</p>
@endsection
