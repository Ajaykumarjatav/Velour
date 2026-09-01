@extends('emails.auth._layout', ['subject' => $subject])
@section('body')
<p class="greeting" style="color:#7c3aed">{{ $headline }}</p>
<p class="text">Hi {{ $staffName }},</p>
<p class="text">Update from <strong>{{ $salonName }}</strong>:</p>

@foreach($lines as $line)
<p class="text" style="margin:8px 0;">{{ $line }}</p>
@endforeach

@if($actionUrl)
<p style="margin:28px 0 8px;">
  <a href="{{ $actionUrl }}"
     style="display:inline-block;background:#7c3aed;color:#fff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600;font-size:14px;">
    {{ $actionLabel }}
  </a>
</p>
@endif

<p class="text" style="margin-top:24px;font-size:13px;color:#6b7280;">If the button does not work, copy this link into your browser:<br>
@if($actionUrl)<span style="word-break:break-all;color:#7c3aed;">{{ $actionUrl }}</span>@endif
</p>
@endsection
