@extends('layouts.admin-tenant')

@section('tenant_module_content')
@include('admin.tenants.partials.breadcrumb', [
    'salon' => $salon,
    'items' => [
        ['label' => $moduleLabel, 'url' => route('admin.tenants.'.$module.'.index', $salon->id)],
        ['label' => '#'.$record->id],
    ],
])

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">{{ $moduleLabel }} details</h3>
        <dl class="space-y-3">
            @foreach($fields as $field)
            <div class="flex justify-between gap-4 text-sm border-b border-gray-800/50 pb-2 last:border-0">
                <dt class="text-gray-500 shrink-0">{{ $field['label'] }}</dt>
                <dd class="text-gray-200 text-right">{{ $field['value'] }}</dd>
            </div>
            @endforeach
        </dl>

        @if($module === 'appointments' && $record->status !== 'cancelled')
        <form method="POST" action="{{ route('admin.tenants.actions.appointment.cancel', [$salon->id, $record->id]) }}" class="mt-6 pt-4 border-t border-gray-800"
              onsubmit="return confirm('Cancel this appointment?')">
            @csrf
            <button type="submit" class="text-xs px-3 py-2 rounded-lg bg-red-900/30 text-red-400 border border-red-800/50 hover:bg-red-900/50">
                Force cancel (admin)
            </button>
        </form>
        @endif

        @if($module === 'pos' && $record->status !== 'refunded')
        <form method="POST" action="{{ route('admin.tenants.actions.pos.refund', [$salon->id, $record->id]) }}" class="mt-4"
              onsubmit="return confirm('Mark as refunded?')">
            @csrf
            <button type="submit" class="text-xs px-3 py-2 rounded-lg bg-amber-900/30 text-amber-400 border border-amber-800/50 hover:bg-amber-900/50">
                Mark refunded (admin)
            </button>
        </form>
        @endif
    </div>

    <div class="space-y-4">
        @if(!empty($related['appointments']) && $related['appointments']->isNotEmpty())
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <h4 class="text-xs font-semibold text-gray-400 uppercase mb-3">Recent appointments</h4>
            <ul class="space-y-2 text-sm">
                @foreach($related['appointments'] as $a)
                <li class="text-gray-300">{{ $a->starts_at?->format('j M') }} · {{ $a->status }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($related['services']) && $related['services']->isNotEmpty())
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <h4 class="text-xs font-semibold text-gray-400 uppercase mb-3">Services</h4>
            <ul class="space-y-1 text-sm text-gray-300">
                @foreach($related['services'] as $s)
                <li>{{ $s->service?->name ?? 'Service' }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($related['items']) && $related['items']->isNotEmpty())
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <h4 class="text-xs font-semibold text-gray-400 uppercase mb-3">Line items</h4>
            <ul class="space-y-2 text-sm">
                @foreach($related['items'] as $item)
                <li class="flex justify-between text-gray-300">
                    <span>{{ $item->name ?? $item->description }}</span>
                    <span>{{ number_format((float)($item->total ?? $item->line_total ?? 0), 2) }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>

<a href="{{ route('admin.tenants.'.$module.'.index', $salon->id) }}" class="inline-block mt-4 text-sm text-gray-500 hover:text-gray-300">← Back to list</a>
@endsection
