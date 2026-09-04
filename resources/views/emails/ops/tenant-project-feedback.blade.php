@extends('emails.auth._layout', ['subject' => 'Tenant project feedback'])
@section('body')
<p class="greeting">New tenant project feedback</p>
<p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.5;">
  A salon admin shared their opinion about the EasyGrox project. Details below.
</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;">
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;width:40%;">Store</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $salon?->name ?? '—' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Store slug</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $salon?->slug ?? '—' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Submitted by</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">{{ $user?->name ?? '—' }}</td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Email</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">
      @if($user?->email)
        <a href="mailto:{{ $user->email }}" style="color:#7c3aed;">{{ $user->email }}</a>
      @else
        —
      @endif
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Rating</td>
    <td style="padding:10px 0;font-size:14px;font-weight:600;color:#111827;">
      {{ $feedback->rating ? $feedback->rating.'/5' : 'Not provided' }}
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;vertical-align:top;">Topics</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">
      @php $topicLabels = $feedback->topicLabels(); @endphp
      {{ $topicLabels ? implode(', ', $topicLabels) : '—' }}
    </td>
  </tr>
  <tr style="border-bottom:1px solid #f1f5f9;">
    <td style="padding:10px 0;font-size:13px;color:#6b7280;vertical-align:top;">Feedback</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;white-space:pre-wrap;">{{ $feedback->message }}</td>
  </tr>
  <tr>
    <td style="padding:10px 0;font-size:13px;color:#6b7280;">Submitted at</td>
    <td style="padding:10px 0;font-size:14px;color:#374151;">{{ $feedback->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</td>
  </tr>
</table>

@if($feedback->id)
<div style="text-align:center;margin-top:24px;">
  <a href="{{ route('admin.tenant-feedback.show', $feedback) }}" class="btn">View in Super Admin →</a>
</div>
@endif

<hr class="divider">
<p class="note">Internal EasyGrox alert · {{ config('app.env') }} · {{ rtrim((string) config('app.url'), '/') }}</p>
@endsection
