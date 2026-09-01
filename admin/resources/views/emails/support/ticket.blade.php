@extends('emails.auth._layout', ['subject' => $emailSubject])

@php
    $statusLabel = ucwords(str_replace('_', ' ', $ticket->status));
    $priorityLabel = ucfirst($ticket->priority);
    $categoryLabel = \App\Models\SupportTicket::categoryLabel($ticket->category);

    $statusBg = match ($ticket->status) {
        'open' => '#dcfce7',
        'in_progress' => '#dbeafe',
        'waiting_on_customer' => '#fef3c7',
        'resolved', 'closed' => '#f3f4f6',
        default => '#ede9fe',
    };
    $statusFg = match ($ticket->status) {
        'open' => '#166534',
        'in_progress' => '#1e40af',
        'waiting_on_customer' => '#92400e',
        'resolved', 'closed' => '#4b5563',
        default => '#5b21b6',
    };
    $priorityBg = match ($ticket->priority) {
        'urgent' => '#fee2e2',
        'high' => '#ffedd5',
        default => '#ede9fe',
    };
    $priorityFg = match ($ticket->priority) {
        'urgent' => '#991b1b',
        'high' => '#9a3412',
        default => '#5b21b6',
    };
@endphp

@section('body')
<p class="greeting">{{ $heading }}</p>
<p class="text">{{ $greetingLine }}</p>
<p class="text" style="margin-top:-8px;">{{ $summary }}</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:8px 0 20px;background:#f8f7ff;border:1px solid #ede9fe;border-radius:12px;">
  <tr>
    <td style="padding:18px 20px;">
      <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#7c3aed;">Ticket details</p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td style="padding:6px 0;font-size:13px;color:#6b7280;width:34%;">Ticket</td>
          <td style="padding:6px 0;font-size:14px;font-weight:700;color:#111827;font-family:ui-monospace,Menlo,monospace;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:13px;color:#6b7280;">Store</td>
          <td style="padding:6px 0;font-size:14px;font-weight:600;color:#111827;">{{ $salonName }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:13px;color:#6b7280;vertical-align:top;">Subject</td>
          <td style="padding:6px 0;font-size:14px;font-weight:600;color:#111827;">{{ $ticket->subject }}</td>
        </tr>
        <tr>
          <td style="padding:10px 0 0;font-size:13px;color:#6b7280;vertical-align:top;">Labels</td>
          <td style="padding:10px 0 0;">
            <span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;background:{{ $statusBg }};color:{{ $statusFg }};">{{ $statusLabel }}</span>
            <span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;background:{{ $priorityBg }};color:{{ $priorityFg }};">{{ $priorityLabel }}</span>
            <span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;background:#ede9fe;color:#5b21b6;">{{ $categoryLabel }}</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

@if($excerpt)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 24px;">
  <tr>
    <td style="padding:16px 18px;background:#fff;border:1px solid #e5e7eb;border-left:4px solid #7c3aed;border-radius:0 10px 10px 0;">
      <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9ca3af;">Message</p>
      <p style="margin:0;font-size:14px;line-height:1.65;color:#374151;white-space:pre-wrap;">{{ $excerpt }}</p>
    </td>
  </tr>
</table>
@endif

<div style="text-align:center;margin:8px 0 12px;">
  <a href="{{ $url }}" class="btn">{{ $ctaLabel }}</a>
</div>

<p class="note" style="text-align:center;word-break:break-all;">
  If the button does not work, open this link:<br>
  <a href="{{ $url }}" style="color:#7c3aed;font-size:12px;">{{ $url }}</a>
</p>

<hr class="divider">
<p class="note">You received this because a support ticket was created or updated for {{ $salonName }}.</p>
@endsection
