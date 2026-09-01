@php
/** @var string $key */
@endphp
@if($key === 'revenue')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
@elseif($key === 'appointments')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5m8 2V5m-9 6h10M5 21h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
@elseif($key === 'staff')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20a5 5 0 0110 0M12 11a3 3 0 100-6 3 3 0 000 6z" />
@elseif($key === 'clients')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19a3 3 0 100-6 3 3 0 000 6zM9 19a3 3 0 100-6 3 3 0 000 6zM12 7a3 3 0 100-6 3 3 0 000 6z" />
@elseif($key === 'inventory')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
@elseif($key === 'marketing')
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
@else
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7.5-3a3.5 3.5 0 110-7 3.5 3.5 0 010 7zM7 7l4 4m0-4l-4 4" />
@endif
