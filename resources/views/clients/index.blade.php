@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('content')

<p class="text-sm text-muted mb-4">{{ number_format($clientTotal) }} total clients</p>

<div class="card p-4 mb-5" x-data="{ openReviewRequest: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="font-semibold text-heading">Get more reviews from your valuable clients</p>
            <p class="text-xs text-muted">Send email-only review requests to clients who have not submitted a review yet.</p>
        </div>
        <button type="button" class="btn-primary" @click="openReviewRequest = true">Request Reviews</button>
    </div>

    <div x-show="openReviewRequest" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="openReviewRequest = false"></div>
        <div class="absolute inset-x-0 top-10 mx-auto max-w-3xl px-4">
            <div class="card p-5 max-h-[80vh] overflow-y-auto">
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
    </div>
</div>

@if(!empty($loyaltyFilterTier))
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-velour-200 dark:border-velour-800 bg-velour-50 dark:bg-velour-900/20 px-4 py-3 text-sm">
        <span class="text-body">Showing <strong class="text-heading">{{ $loyaltyFilterTier->name }}</strong> members</span>
        <a href="{{ route('clients.index', request()->except('loyalty_tier_id')) }}" class="text-link font-medium">Clear filter</a>
    </div>
@endif

<div class="flex flex-col lg:flex-row gap-4 mb-6 items-start">
    <form action="{{ route('clients.index') }}" method="GET" class="flex flex-1 flex-col sm:flex-row gap-3 min-w-0 w-full">
        @if(request('loyalty_tier_id'))
            <input type="hidden" name="loyalty_tier_id" value="{{ request('loyalty_tier_id') }}">
        @endif
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone…" class="form-input w-full min-w-0 flex-1">
        <div class="flex flex-wrap gap-2 shrink-0">
            <button type="submit" class="btn-secondary">Search</button>
            @if($search)<a href="{{ route('clients.index') }}" class="btn-outline">Clear</a>@endif
        </div>
    </form>
    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data" class="inline shrink-0">
        @csrf
        <label for="client-csv-upload" class="btn-outline cursor-pointer w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </label>
        <input id="client-csv-upload" type="file" name="file" accept=".csv,text/csv,text/plain,.txt" class="hidden" onchange="if(this.files.length)this.form.submit()">
    </form>
    <a href="{{ route('clients.export') }}" class="btn-outline flex-shrink-0 w-full sm:w-auto">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export
    </a>
    <a href="{{ route('clients.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">+ Add Client</a>
</div>

@php
    $appointmentsMap = isset($appointmentsByClient) ? $appointmentsByClient : collect();
    $clientRows = $clients->map(function ($c) use ($salon, $appointmentsMap) {
        return [
            'id' => (int) $c->id,
            'name' => trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')),
            'first_name' => (string) ($c->first_name ?? ''),
            'last_name' => (string) ($c->last_name ?? ''),
            'initial' => strtoupper(substr((string) ($c->first_name ?? '?'), 0, 1)),
            'email' => $c->email,
            'phone' => $c->phone,
            'added' => $c->created_at?->format('d M Y'),
            'marketing' => (bool) $c->marketing_consent,
            'visits' => (int) ($c->visit_count ?? 0),
            'total_spent' => number_format((float) ($c->total_spent ?? 0), 2, '.', ''),
            'last_visit' => $c->last_visit_at ? $c->last_visit_at->format('d M Y') : '—',
            'dob' => $c->date_of_birth ? $c->date_of_birth->format('d M Y') : '—',
            'gender' => $c->gender ? str_replace('_', ' ', (string) $c->gender) : '—',
            'address' => $c->address,
            'status' => $c->status ?: 'active',
            'source' => $c->source ?: '—',
            'is_vip' => (bool) ($c->is_vip ?? false),
            'notes' => $c->notes,
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
                    'amount' => \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP') . number_format((float) ($apt->total_price ?? 0), 2),
                    'status' => ucfirst(str_replace('_', ' ', (string) ($apt->status ?? 'pending'))),
                ];
            })->values()->all(),
        ];
    })->values();
    $firstClientId = optional($clients->first())->id;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4"
     x-data="{
        selectedClientId: {{ $firstClientId ? (int) $firstClientId : 'null' }},
        clients: @js($clientRows),
        loyaltyOptions: @js(($loyaltyTiers ?? collect())->map(fn($t) => ['id' => (string) $t->id, 'name' => (string) $t->name])->values()),
        editMode: false,
        editForm: { first_name: '', last_name: '', email: '', phone: '', notes: '', loyalty_tier_id: '', marketing_consent: true },
        selectedClient() {
            return this.clients.find(c => c.id === this.selectedClientId) || this.clients[0] || null;
        },
        selectClient(id) {
            this.selectedClientId = id;
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
    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>Name</th>
                <th class="hidden sm:table-cell">Contact</th>
                <th class="hidden md:table-cell">Added</th>
                <th scope="col" class="hidden lg:table-cell">
                    <span class="inline-flex items-center gap-1">
                        Marketing
                        <x-marketing-consent-help mode="tooltip" />
                    </span>
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse($clients as $client)
            <tr @click="selectClient({{ (int) $client->id }})"
                :class="selectedClientId === {{ (int) $client->id }} ? 'bg-velour-50/50 dark:bg-velour-900/20' : ''"
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
                <td class="hidden sm:table-cell text-body">{{ $client->phone ?? '—' }}</td>
                <td class="hidden md:table-cell text-muted text-xs">{{ $client->created_at->format('d M Y') }}</td>
                <td class="hidden lg:table-cell">
                    @if($client->marketing_consent)
                        <span class="badge-green">Opted in</span>
                    @else
                        <span class="badge-gray">Opted out</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-muted">No clients found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-5 min-h-[18rem]" x-show="selectedClient()" x-cloak>
        <template x-if="editMode && selectedClient()">
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
                    <p class="form-hint">Used for marketing member counts and optional checkout discounts.</p>
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
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold text-base flex-shrink-0" x-text="selectedClient().initial"></div>
                        <div class="min-w-0">
                            <p class="font-semibold text-heading truncate" x-text="selectedClient().name"></p>
                            <p class="text-xs text-muted truncate" x-text="selectedClient().email || '—'"></p>
                            <p class="text-xs text-muted" x-text="selectedClient().phone || '—'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="startEdit()" class="btn-outline text-xs">Edit</button>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-3 text-center">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Visits</p>
                        <p class="mt-1 text-lg font-semibold text-heading" x-text="selectedClient().visits"></p>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-3 text-center">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Total Spent</p>
                        <p class="mt-1 text-lg font-semibold text-heading">
                            <span x-text="selectedClient().currency_symbol"></span><span x-text="selectedClient().total_spent"></span>
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-3 text-center">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Last Visit</p>
                        <p class="mt-1 text-sm font-semibold text-heading" x-text="selectedClient().last_visit"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Marketing</p>
                    <span class="mt-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="selectedClient().marketing ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                          x-text="selectedClient().marketing ? 'Opted in' : 'Opted out'"></span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Date of Birth</p>
                        <p class="mt-1 text-body capitalize" x-text="selectedClient().dob"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Gender</p>
                        <p class="mt-1 text-body capitalize" x-text="selectedClient().gender"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Status</p>
                        <p class="mt-1 text-body capitalize" x-text="selectedClient().status"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Source</p>
                        <p class="mt-1 text-body" x-text="selectedClient().source"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Address</p>
                    <p class="mt-1 text-sm text-body" x-text="selectedClient().address || '—'"></p>
                </div>

                <div class="mt-4" x-show="selectedClient().is_vip">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">VIP Client</span>
                </div>

                <div class="mt-4">
                    <p class="text-[11px] uppercase tracking-wide text-muted">Notes</p>
                    <p class="mt-1 text-sm text-body" x-text="selectedClient().notes || 'No notes added.'"></p>
                </div>

                <div class="mt-5 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800">
                        <h4 class="font-semibold text-heading">Appointments</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-xs uppercase tracking-wide text-muted bg-gray-50/80 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Services</th>
                                    <th class="px-4 py-2 text-left">Staff</th>
                                    <th class="px-4 py-2 text-left">Amount</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="apt in (selectedClient().appointments || [])" :key="apt.date + apt.time + apt.staff">
                                    <tr class="border-t border-gray-200/60 dark:border-gray-800/70">
                                        <td class="px-4 py-2 text-body">
                                            <div x-text="apt.date"></div>
                                            <div class="text-xs text-muted" x-text="apt.time"></div>
                                        </td>
                                        <td class="px-4 py-2 text-body" x-text="apt.services"></td>
                                        <td class="px-4 py-2 text-body" x-text="apt.staff"></td>
                                        <td class="px-4 py-2 text-body" x-text="apt.amount"></td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                  x-text="apt.status"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!selectedClient().appointments || selectedClient().appointments.length === 0">
                                    <td colspan="5" class="px-4 py-4 text-center text-sm text-muted">No appointments yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<div class="mt-4">{{ $clients->links() }}</div>
@endsection
