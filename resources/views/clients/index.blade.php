@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('content')
@php
    $isScopedStaffPanel = auth()->user()?->dashboardScopedStaffId() !== null;
@endphp

@php
    $engagementFilter = $engagementFilter ?? null;
    $engagementWindowDays = $engagementWindowDays ?? 90;
    $engagementQuery = fn (array $extra = []) => route('clients.index', array_filter(array_merge(
        request()->only(['search', 'sort', 'dir', 'loyalty_tier_id']),
        $extra
    )));
@endphp

{{-- Row 1: Compact engagement stats --}}
<div class="grid grid-cols-3 gap-2 sm:gap-3 mb-3">
    <a href="{{ $engagementQuery() }}"
       title="All clients in your salon"
       class="rounded-xl border px-3 py-2.5 transition-all {{ ! $engagementFilter ? 'border-velour-400 bg-velour-50/80 dark:bg-velour-950/30 ring-1 ring-velour-200 dark:ring-velour-800' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/50 hover:border-velour-300' }}">
        <p class="text-[10px] font-semibold uppercase tracking-[0.06em] text-muted">All clients</p>
        <p class="mt-0.5 text-xl font-bold text-heading tabular-nums leading-none">{{ number_format($clientTotal) }}</p>
    </a>
    <a href="{{ $engagementQuery(['engagement' => 'active']) }}"
       title="Visited in last {{ $engagementWindowDays }} days or has upcoming booking"
       class="rounded-xl border px-3 py-2.5 transition-all {{ $engagementFilter === 'active' ? 'border-emerald-400 bg-emerald-50/80 dark:bg-emerald-950/25 ring-1 ring-emerald-200 dark:ring-emerald-800' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/50 hover:border-emerald-300' }}">
        <p class="text-[10px] font-semibold uppercase tracking-[0.06em] text-emerald-700 dark:text-emerald-400">Active</p>
        <p class="mt-0.5 text-xl font-bold text-heading tabular-nums leading-none">{{ number_format($engagementActiveCount ?? 0) }}</p>
        <p class="mt-1 text-[10px] text-muted leading-tight hidden sm:block">Recent visit or booking</p>
    </a>
    <a href="{{ $engagementQuery(['engagement' => 'inactive']) }}"
       title="No visit in {{ $engagementWindowDays }} days and no upcoming booking"
       class="rounded-xl border px-3 py-2.5 transition-all {{ $engagementFilter === 'inactive' ? 'border-amber-400 bg-amber-50/80 dark:bg-amber-950/25 ring-1 ring-amber-200 dark:ring-amber-800' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/50 hover:border-amber-300' }}">
        <p class="text-[10px] font-semibold uppercase tracking-[0.06em] text-amber-700 dark:text-amber-400">Inactive</p>
        <p class="mt-0.5 text-xl font-bold text-heading tabular-nums leading-none">{{ number_format($engagementInactiveCount ?? 0) }}</p>
        <p class="mt-1 text-[10px] text-muted leading-tight hidden sm:block">No recent activity</p>
    </a>
</div>

@if(!empty($loyaltyFilterTier))
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-velour-200/90 dark:border-velour-500/20 bg-velour-50 dark:bg-velour-950/35 px-4 py-2.5 text-[13px] leading-snug">
        <span class="text-body">Showing <strong class="text-heading">{{ $loyaltyFilterTier->name }}</strong> members</span>
        <a href="{{ route('clients.index', request()->except('loyalty_tier_id')) }}" class="text-link font-semibold shrink-0 hover:underline">Clear filter</a>
    </div>
@endif

{{-- Row 2: Search + actions (+ review requests) --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 px-3 py-2.5 sm:px-4 sm:py-3 mb-5 shadow-sm dark:shadow-none"
     @if(!($isScopedStaffPanel ?? false)) x-data="{ openReviewRequest: false }" @endif>
    <div class="flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-3">
        <form action="{{ route('clients.index') }}" method="GET" class="flex flex-1 flex-wrap items-center gap-2 min-w-0">
            @if(request('loyalty_tier_id'))
                <input type="hidden" name="loyalty_tier_id" value="{{ request('loyalty_tier_id') }}">
            @endif
            @if($engagementFilter)
                <input type="hidden" name="engagement" value="{{ $engagementFilter }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone…" class="form-input w-full min-w-0 sm:flex-1 sm:min-w-[10rem] lg:max-w-md">
            <div class="flex w-full sm:w-auto gap-2 shrink-0">
                <button type="submit" class="btn-secondary flex-1 sm:flex-initial min-w-0">Search</button>
                @if($search)<a href="{{ route('clients.index') }}" class="btn-outline flex-1 sm:flex-initial min-w-0 text-center">Clear</a>@endif
            </div>
        </form>
        <div class="flex flex-wrap items-center gap-2 shrink-0 lg:border-l lg:border-gray-200 lg:dark:border-gray-800 lg:pl-3">
            @if(!($isScopedStaffPanel ?? false))
            <button type="button" class="btn-outline whitespace-nowrap" @click="openReviewRequest = true" title="Send email review requests to eligible clients">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Request Reviews
            </button>
            <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data" class="inline-flex shrink-0 min-w-0">
                @csrf
                <label for="client-csv-upload" class="btn-outline cursor-pointer inline-flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </label>
                <input id="client-csv-upload" type="file" name="file" accept=".csv,text/csv,text/plain,.txt" class="hidden" onchange="if(this.files.length)this.form.submit()">
            </form>
            <a href="{{ route('clients.export') }}" class="btn-outline inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </a>
            @endif
            <a href="{{ route('clients.create') }}" class="btn-primary whitespace-nowrap">+ Add Client</a>
        </div>
    </div>

    @if(!($isScopedStaffPanel ?? false))
    <x-modal-overlay show="openReviewRequest" @click.self="openReviewRequest = false">
        <div class="w-full max-w-3xl" @click.stop>
            <div class="card p-6 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-heading">Select clients for review request</h3>
                    <button type="button" class="btn-outline btn-sm" @click="openReviewRequest = false">Close</button>
                </div>
                <p class="text-xs text-muted mb-3">Clients who already reviewed are visible but locked (view-only). Channel: Email only.</p>

                <form action="{{ route('clients.review-requests.send') }}" method="POST">
                    @csrf
                    <div class="space-y-2 max-h-[50vh] overflow-y-auto pr-1">
                        @forelse(($reviewRequestClients ?? collect()) as $row)
                            @php
                                $statusLabel = $row['already_reviewed'] ? 'Already reviewed' : ($row['has_email'] ? 'Eligible' : 'No email');
                                $statusClass = $row['already_reviewed']
                                    ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
                                    : ($row['has_email']
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300');
                            @endphp
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 {{ $row['can_request'] ? 'cursor-pointer' : 'opacity-75 cursor-not-allowed' }}">
                                <input type="checkbox"
                                       name="client_ids[]"
                                       value="{{ (int) $row['id'] }}"
                                       class="rounded border-gray-300 text-velour-600"
                                       {{ $row['can_request'] ? '' : 'disabled' }}>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-body truncate">{{ $row['name'] }}</p>
                                    <p class="text-xs text-muted truncate">{{ $row['email'] ?: 'No email address' }}</p>
                                </div>
                                <span class="text-[11px] px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-muted">No clients found.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <button type="submit" class="btn-primary">Send Review Request (Email)</button>
                        <button type="button" class="btn-outline" @click="openReviewRequest = false">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal-overlay>
    @endif
</div>

@php
    $appointmentsMap = isset($appointmentsByClient) ? $appointmentsByClient : collect();
    $historyMap = $historyByClient ?? [];
    $clientRows = $clients->map(function ($c) use ($salon, $appointmentsMap, $historyMap, $isScopedStaffPanel, $engagementCutoff, $clientsWithUpcoming, $engagementWindowDays) {
        $hasRecentVisit = $c->last_visit_at !== null && $c->last_visit_at->gte($engagementCutoff);
        $hasUpcoming = ($clientsWithUpcoming ?? collect())->has((int) $c->id);
        $isEngagementActive = $hasRecentVisit || $hasUpcoming;
        if ($isEngagementActive) {
            $statusHint = $hasRecentVisit
                ? 'Visited within the last '.$engagementWindowDays.' days'
                : 'Has an upcoming appointment';
        } elseif ($c->last_visit_at === null) {
            $statusHint = 'No completed visit yet (outside '.$engagementWindowDays.'-day window)';
        } else {
            $statusHint = 'Last visit more than '.$engagementWindowDays.' days ago';
        }

        return [
            'id' => (int) $c->id,
            'name' => trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')),
            'first_name' => (string) ($c->first_name ?? ''),
            'last_name' => (string) ($c->last_name ?? ''),
            'initial' => strtoupper(substr((string) ($c->first_name ?? '?'), 0, 1)),
            'email' => $isScopedStaffPanel ? null : $c->email,
            'phone' => $isScopedStaffPanel ? null : $c->phone,
            'added' => $c->created_at?->format('d M Y'),
            'marketing' => (bool) $c->marketing_consent,
            'visits' => (int) ($c->visit_count ?? 0),
            'total_spent' => number_format((float) ($c->transactions_total ?? $c->total_spent ?? 0), 2, '.', ''),
            'last_visit' => $c->last_visit_at ? $c->last_visit_at->format('d M Y') : '—',
            'dob' => $isScopedStaffPanel ? '—' : ($c->date_of_birth ? $c->date_of_birth->format('d M Y') : '—'),
            'gender' => $isScopedStaffPanel ? '—' : ($c->gender ? str_replace('_', ' ', (string) $c->gender) : '—'),
            'address' => $isScopedStaffPanel ? null : $c->address,
            'status' => $isEngagementActive ? 'Active' : 'Inactive',
            'status_key' => $isEngagementActive ? 'active' : 'inactive',
            'status_hint' => $statusHint,
            'account_status' => $c->status ?: 'active',
            'source' => $c->source ?: '—',
            'is_vip' => (bool) ($c->is_vip ?? false),
            'notes' => $isScopedStaffPanel ? null : $c->notes,
            'loyalty_tier_id' => $c->loyalty_tier_id ? (string) $c->loyalty_tier_id : '',
            'show_url' => route('clients.show', $c->id),
            'update_url' => route('clients.update', $c->id),
            'edit_url' => route('clients.edit', $c->id),
            'currency_symbol' => \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP'),
            'appointments' => collect($appointmentsMap->get($c->id, collect()))->map(function ($apt) use ($salon) {
                return [
                    'date' => $apt->starts_at ? $apt->starts_at->format('d M Y') : '—',
                    'time' => $apt->starts_at ? $apt->starts_at->format('H:i') : '—',
                    'services' => $apt->services->pluck('service_name')->filter()->join(', ') ?: '—',
                    'staff' => $apt->staff?->name ?? '—',
                    'amount' => \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP') . number_format(
                        (float) (($apt->payment_status === 'paid' && (float) ($apt->amount_paid ?? 0) > 0)
                            ? $apt->amount_paid
                            : ($apt->total_price ?? 0)),
                        2
                    ),
                    'status' => ucfirst(str_replace('_', ' ', (string) ($apt->status ?? 'pending'))),
                ];
            })->values()->all(),
            'history' => collect($historyMap[(int) $c->id] ?? [])->map(function ($row) use ($salon) {
                $at = ! empty($row['at']) ? \Carbon\Carbon::parse($row['at']) : null;

                return [
                    'kind' => $row['kind'],
                    'date' => $at ? $at->format('d M Y') : '—',
                    'time' => $at ? $at->format('H:i') : '',
                    'label' => $row['label'],
                    'detail' => $row['detail'],
                    'amount' => \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP').number_format((float) ($row['amount'] ?? 0), 2),
                    'status' => $row['status'],
                    'url' => $row['url'] ?? null,
                ];
            })->values()->all(),
        ];
    })->values();
    $firstClientId = optional($clients->first())->id;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 xl:gap-6"
     x-data="{
        selectedClientId: {{ $firstClientId ? (int) $firstClientId : 'null' }},
        isScopedStaff: {{ ($isScopedStaffPanel ?? false) ? 'true' : 'false' }},
        clients: @js($clientRows),
        visibleHistory: [],
        loyaltyOptions: @js(($loyaltyTiers ?? collect())->map(fn($t) => ['id' => (string) $t->id, 'name' => (string) $t->name])->values()),
        editMode: false,
        editForm: { first_name: '', last_name: '', email: '', phone: '', notes: '', loyalty_tier_id: '', marketing_consent: true },
        init() {
            this.syncHistory();
        },
        selectedClient() {
            const id = Number(this.selectedClientId);
            return this.clients.find(c => Number(c.id) === id) || this.clients[0] || null;
        },
        syncHistory() {
            const client = this.selectedClient();
            this.visibleHistory = Array.isArray(client?.history) ? client.history : [];
        },
        selectClient(id) {
            this.selectedClientId = Number(id);
            this.syncHistory();
            if (this.editMode) {
                this.startEdit();
            }
        },
        startEdit() {
            const c = this.selectedClient();
            if (!c) return;
            this.editForm = {
                first_name: c.first_name || '',
                last_name: c.last_name || '',
                email: c.email || '',
                phone: c.phone || '',
                notes: c.notes || '',
                loyalty_tier_id: String(c.loyalty_tier_id || ''),
                marketing_consent: !!c.marketing,
            };
            this.editMode = true;
        },
        cancelEdit() {
            this.editMode = false;
        }
     }">
    <div class="table-wrap [&_thead_th]:py-3 [&_thead_th]:px-5 [&_tbody_td]:py-2.5 [&_tbody_td]:px-5">
        <table class="data-table">
            <thead>
            <tr>
                <th>Name</th>
                @if(!$isScopedStaffPanel)
                <th class="hidden sm:table-cell">Contact</th>
                @endif
                <th class="hidden md:table-cell">Added</th>
                @if(!$isScopedStaffPanel)
                <th scope="col" class="hidden lg:table-cell">
                    <span class="inline-flex items-center gap-1">
                        Marketing
                        <x-marketing-consent-help mode="tooltip" />
                    </span>
                </th>
                @endif
            </tr>
            </thead>
            <tbody>
            @forelse($clients as $client)
            <tr @click="selectClient({{ (int) $client->id }})"
                :class="selectedClientId === {{ (int) $client->id }}
                    ? 'bg-velour-50/95 dark:bg-velour-950/35 ring-1 ring-inset ring-velour-200/80 dark:ring-velour-500/25 hover:bg-velour-100/85 dark:hover:bg-velour-950/45'
                    : 'hover:bg-gray-50/90 dark:hover:bg-gray-800/40'"
                class="cursor-pointer transition-colors">
                <td>
                    <div class="flex items-center gap-3 min-h-[2.75rem]">
                        <div class="w-9 h-9 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($client->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-heading">{{ $client->first_name }} {{ $client->last_name }}</p>
                            @if($client->email)<p class="text-xs text-muted">{{ $client->email }}</p>@endif
                        </div>
                    </div>
                </td>
                @if(!$isScopedStaffPanel)
                <td class="hidden sm:table-cell text-body">{{ $client->phone ?? '—' }}</td>
                @endif
                <td class="hidden md:table-cell text-muted text-xs">{{ $client->created_at->format('d M Y') }}</td>
                @if(!$isScopedStaffPanel)
                <td class="hidden lg:table-cell">
                    @if($client->marketing_consent)
                        <span class="badge-green">Opted in</span>
                    @else
                        <span class="badge-gray">Opted out</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr><td colspan="{{ $isScopedStaffPanel ? 2 : 4 }}" class="px-6 py-14 text-center text-sm text-muted">No clients found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-6 min-h-[18rem] shadow-sm dark:shadow-none" x-show="selectedClient()" x-cloak>
        <template x-if="!isScopedStaff && editMode && selectedClient()">
            <form :action="selectedClient().update_url" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="inline_edit" value="1">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-heading">Edit Client</h3>
                    <button type="button" @click="cancelEdit()" class="inline-flex items-center gap-1 text-sm font-medium text-link">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                        </svg>
                        Back
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">First name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" x-model="editForm.first_name" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Last name <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" x-model="editForm.last_name" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" x-model="editForm.email" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" x-model="editForm.phone" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" x-model="editForm.notes" class="form-textarea"></textarea>
                </div>

                <div>
                    <label class="form-label">Loyalty plan</label>
                    <select name="loyalty_tier_id" x-model="editForm.loyalty_tier_id" class="form-select">
                        <option value="">— None —</option>
                        <template x-for="tier in loyaltyOptions" :key="tier.id">
                            <option :value="tier.id" x-text="tier.name"></option>
                        </template>
                    </select>
                    <p class="form-hint">Manage plans under <a href="{{ route('service-packages.index', ['section' => 'loyalty']) }}" class="text-velour-600 dark:text-velour-400 font-medium hover:underline">Plans/Packages → Loyalty plans</a>.</p>
                </div>

                <div>
                    <input type="hidden" name="marketing_consent" value="0">
                    <label class="inline-flex items-start gap-2 text-sm text-body cursor-pointer">
                        <input type="checkbox" name="marketing_consent" value="1" x-model="editForm.marketing_consent"
                               class="mt-0.5 rounded border-gray-300 text-velour-600">
                        <span>
                            <span class="font-medium">Marketing consent</span>
                            <span class="block text-muted">Records whether this client agreed to receive promotional messages from your salon.</span>
                        </span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <button type="button" @click="cancelEdit()" class="btn-outline">Cancel</button>
                </div>
            </form>
        </template>
        <template x-if="selectedClient()">
            <div x-show="!editMode">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-full bg-velour-100 dark:bg-velour-900/45 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold text-base flex-shrink-0 ring-2 ring-white/10 dark:ring-gray-950/30" x-text="selectedClient().initial"></div>
                        <div class="min-w-0 space-y-0.5">
                            <p class="font-semibold text-heading truncate tracking-tight" x-text="selectedClient().name"></p>
                            <template x-if="!isScopedStaff">
                                <p class="text-xs text-muted truncate" x-text="selectedClient().email || '—'"></p>
                            </template>
                            <template x-if="!isScopedStaff">
                                <p class="text-xs text-muted tabular-nums" x-text="selectedClient().phone || '—'"></p>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button x-show="!isScopedStaff" type="button" @click="startEdit()" class="btn-outline btn-sm whitespace-nowrap">Edit</button>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-2.5 sm:gap-3">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Visits</p>
                        <p class="mt-1.5 text-lg font-semibold tabular-nums text-heading" x-text="selectedClient().visits"></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Total Spent</p>
                        <p class="mt-1.5 text-lg font-semibold tabular-nums text-heading">
                            <span x-text="selectedClient().currency_symbol"></span><span x-text="selectedClient().total_spent"></span>
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Last Visit</p>
                        <p class="mt-1.5 text-sm font-semibold text-heading" x-text="selectedClient().last_visit"></p>
                    </div>
                </div>

                <div class="mt-4" x-show="!isScopedStaff">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Marketing</p>
                    <span class="mt-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="selectedClient().marketing ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                          x-text="selectedClient().marketing ? 'Opted in' : 'Opted out'"></span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3.5 text-sm" x-show="!isScopedStaff">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Date of Birth</p>
                        <p class="mt-1 text-body capitalize" x-text="selectedClient().dob"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Gender</p>
                        <p class="mt-1 text-body capitalize" x-text="selectedClient().gender"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Client activity</p>
                        <p class="mt-1">
                            <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full"
                                  :class="selectedClient().status_key === 'active'
                                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                      : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'"
                                  x-text="selectedClient().status"></span>
                        </p>
                        <p class="mt-1 text-[11px] text-muted" x-text="selectedClient().status_hint"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Source</p>
                        <p class="mt-1 text-body" x-text="selectedClient().source"></p>
                    </div>
                </div>

                <div class="mt-4" x-show="!isScopedStaff">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Address</p>
                    <p class="mt-1 text-sm text-body" x-text="selectedClient().address || '—'"></p>
                </div>

                <div class="mt-4" x-show="selectedClient().is_vip">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">VIP Client</span>
                </div>

                <div class="mt-4" x-show="!isScopedStaff">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Notes</p>
                    <p class="mt-1 text-sm text-body" x-text="selectedClient().notes || 'No notes added.'"></p>
                </div>

                <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/90 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <h4 class="text-sm font-semibold tracking-tight text-heading">History</h4>
                        <p class="text-[11px] text-muted mt-0.5">Appointments and POS sales show the full amount charged (including extra quantity on the bill).</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-[11px] font-semibold uppercase tracking-wider text-muted bg-gray-50/80 dark:bg-gray-900/45">
                                <tr>
                                    <th class="px-5 py-2.5 text-left">Date</th>
                                    <th class="px-5 py-2.5 text-left">Type</th>
                                    <th class="px-5 py-2.5 text-left">Details</th>
                                    <th class="px-5 py-2.5 text-left">Amount</th>
                                    <th class="px-5 py-2.5 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in visibleHistory" :key="'history-' + idx + '-' + (row.kind || '')">
                                    <tr class="border-t border-gray-100 dark:border-gray-800/80 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-5 py-2.5 text-body">
                                            <div x-text="row.date"></div>
                                            <div class="text-xs text-muted" x-text="row.time"></div>
                                        </td>
                                        <td class="px-5 py-2.5 text-body font-medium" x-text="row.label"></td>
                                        <td class="px-5 py-2.5 text-body text-xs" x-text="row.detail"></td>
                                        <td class="px-5 py-2.5 text-body tabular-nums font-semibold" x-text="row.amount"></td>
                                        <td class="px-5 py-2.5">
                                            <a x-show="row.url" :href="row.url" class="text-link text-xs font-medium">View</a>
                                            <span x-show="!row.url" class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                  x-text="row.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="visibleHistory.length === 0">
                                    <td colspan="5" class="px-5 py-6 text-center text-sm text-muted">No history yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<div class="mt-6 flex justify-center sm:justify-end">{{ $clients->links() }}</div>
@endsection
