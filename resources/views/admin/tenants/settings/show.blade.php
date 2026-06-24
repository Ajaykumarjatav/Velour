@extends('layouts.admin-tenant')

@section('tenant_module_content')
@include('admin.tenants.partials.breadcrumb', ['salon' => $salon, 'items' => [['label' => 'Settings']]])

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Salon profile</h3>
    <dl class="space-y-2 text-sm mb-6">
        @foreach([
            'Email' => $salon->email ?? '—',
            'Phone' => $salon->phone ?? '—',
            'City' => $salon->city ?? '—',
            'Timezone' => $salon->timezone ?? '—',
            'Currency' => strtoupper($salon->currency ?? 'GBP'),
            'Slug' => $salon->slug,
            'Domain' => $salon->domain ?? '—',
        ] as $label => $value)
        <div class="flex justify-between gap-3 border-b border-gray-800/50 pb-2">
            <dt class="text-gray-500">{{ $label }}</dt>
            <dd class="text-gray-200 text-right">{{ $value }}</dd>
        </div>
        @endforeach
    </dl>

    @if($settings->isNotEmpty())
    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Stored settings</h3>
    <dl class="space-y-2 text-sm max-h-96 overflow-y-auto">
        @foreach($settings as $key => $value)
        <div class="flex justify-between gap-3 border-b border-gray-800/50 pb-2">
            <dt class="text-gray-500 font-mono text-xs">{{ $key }}</dt>
            <dd class="text-gray-300 text-right text-xs max-w-[60%] truncate" title="{{ is_array($value) ? json_encode($value) : $value }}">
                {{ is_array($value) ? json_encode($value) : \Illuminate\Support\Str::limit((string)$value, 80) }}
            </dd>
        </div>
        @endforeach
    </dl>
    @else
    <p class="text-sm text-gray-500">No custom settings keys stored.</p>
    @endif
</div>
@endsection
