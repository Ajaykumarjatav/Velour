@php
    $key = $col;
    $currency = strtoupper($salon->currency ?? 'GBP');
    $sym = $currency === 'INR' ? '₹' : ($currency === 'GBP' ? '£' : $currency.' ');
@endphp
@switch($module)
    @case('clients')
        @if($key === 'name'){{ $row->full_name }}
        @elseif($key === 'total_spent'){{ $sym }}{{ number_format((float)$row->total_spent, 2) }}
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('appointments')
        @if($key === 'starts_at'){{ $row->starts_at?->format('j M Y H:i') ?? '—' }}
        @elseif($key === 'client'){{ $row->client?->full_name ?? '—' }}
        @elseif($key === 'staff'){{ $row->staff?->name ?? '—' }}
        @elseif($key === 'total_price'){{ $sym }}{{ number_format((float)$row->total_price, 2) }}
        @else<span class="capitalize">{{ $row->{$key} ?? '—' }}</span>@endif
        @break
    @case('staff')
        @if($key === 'name'){{ $row->name }}
        @elseif($key === 'is_active'){{ $row->is_active ? 'Yes' : 'No' }}
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('pos')
        @if($key === 'created_at'){{ $row->created_at?->format('j M Y H:i') }}
        @elseif($key === 'client'){{ $row->client?->full_name ?? 'Walk-in' }}
        @elseif($key === 'total'){{ $sym }}{{ number_format((float)$row->total, 2) }}
        @else<span class="capitalize">{{ $row->{$key} ?? '—' }}</span>@endif
        @break
    @case('services')
        @if($key === 'category'){{ $row->category?->name ?? '—' }}
        @elseif($key === 'price'){{ $sym }}{{ number_format((float)$row->price, 2) }}
        @elseif($key === 'is_active'){{ $row->is_active ? 'Yes' : 'No' }}
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('inventory')
        @if($key === 'retail_price'){{ $sym }}{{ number_format((float)$row->retail_price, 2) }}
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('expenses')
        @if($key === 'expense_date'){{ $row->expense_date?->format('j M Y') }}
        @elseif($key === 'category'){{ $row->category?->name ?? '—' }}
        @elseif($key === 'amount'){{ $sym }}{{ number_format((float)$row->amount, 2) }}
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('reviews')
        @if($key === 'created_at'){{ $row->created_at?->format('j M Y') }}
        @elseif($key === 'client'){{ $row->client?->full_name ?? '—' }}
        @elseif($key === 'comment')<span class="line-clamp-2">{{ $row->comment ?? '—' }}</span>
        @else{{ $row->{$key} ?? '—' }}@endif
        @break
    @case('marketing')
        @if($key === 'created_at'){{ $row->created_at?->format('j M Y') }}
        @else<span class="capitalize">{{ $row->{$key} ?? '—' }}</span>@endif
        @break
    @case('leave')
        @if($key === 'staff'){{ $row->staff?->name ?? '—' }}
        @elseif($key === 'dates'){{ $row->start_date?->format('j M') }} – {{ $row->end_date?->format('j M Y') }}
        @else<span class="capitalize">{{ $row->{$key} ?? '—' }}</span>@endif
        @break
    @case('attendance')
        @if($key === 'staff'){{ $row->staff?->name ?? '—' }}
        @elseif($key === 'attendance_date'){{ $row->attendance_date?->format('j M Y') }}
        @elseif($key === 'clock_in_at'){{ $row->clock_in_at?->format('H:i') ?? '—' }}
        @else<span class="capitalize">{{ $row->{$key} ?? '—' }}</span>@endif
        @break
    @default
        {{ $row->{$key} ?? '—' }}
@endswitch
