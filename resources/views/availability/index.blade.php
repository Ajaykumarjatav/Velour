@extends('layouts.app')
@section('title', 'Availability & Resources')
@section('page-title', 'Availability & Resources')

@php
    use App\Http\Controllers\Web\AvailabilityResourcesController;

    $days = AvailabilityResourcesController::WEEK_DAYS;
    $staffInitials = function ($s) {
        $i = trim((string) ($s->initials ?? ''));
        if ($i !== '') {
            return strtoupper(mb_substr($i, 0, 2));
        }

        return strtoupper(mb_substr($s->first_name, 0, 1) . mb_substr($s->last_name, 0, 1));
    };
    $staffDayOpen = function ($st, string $d): bool {
        $wd = $st->working_days;

        return $wd === null || in_array($d, $wd, true);
    };
    $resourceBaseUrl = rtrim(url('availability/resources'), '/');
@endphp

@section('content')
<div class="space-y-5 max-w-6xl mx-auto w-full"
     x-data="{
        leaveOpen: false,
        resourceOpen: false,
        resourceEditId: null,
        resourceName: '',
        resourceType: 'room',
        resourceCapacity: 1,
        resourceEquipment: '',
        resourceBookable: true,
        resourceStatus: 'active',
        resourceAvailability: 'available',
        openLeave() { this.leaveOpen = true; },
        closeLeave() { this.leaveOpen = false; },
        openResourceAdd() {
            this.resourceEditId = null;
            this.resourceName = '';
            this.resourceType = 'room';
            this.resourceCapacity = 1;
            this.resourceEquipment = '';
            this.resourceBookable = true;
            this.resourceStatus = 'active';
            this.resourceAvailability = 'available';
            this.resourceOpen = true;
        },
        openResourceEdit(r) {
            this.resourceEditId = r.id;
            this.resourceName = r.name;
            this.resourceType = r.type;
            this.resourceCapacity = r.capacity;
            this.resourceEquipment = (r.equipment && r.equipment.length) ? r.equipment.join(', ') : '';
            this.resourceBookable = !!r.bookable;
            this.resourceStatus = r.status;
            this.resourceAvailability = r.availability_status;
            this.resourceOpen = true;
        },
        closeResource() { this.resourceOpen = false; },
        resourceAction() {
            return this.resourceEditId
                ? '{{ $resourceBaseUrl }}/' + this.resourceEditId
                : @js(route('availability.resources.store'));
        }
     }">

    <div class="card p-4 sm:p-5">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="font-serif text-xl sm:text-2xl text-heading tracking-tight leading-tight">Availability &amp; Resources</h1>
                    <p class="text-sm text-muted mt-1">Staff schedules, rooms, chairs &amp; buffer rules</p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <button type="button" @click="openLeave()" class="btn-outline inline-flex items-center justify-center gap-2 text-sm px-3 py-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Block / Leave
                    </button>
                    <button type="button" @click="openResourceAdd()" class="btn-primary text-sm px-3 py-2">+ Add Resource</button>
                </div>
            </div>
            {{-- Tabs — grouped rail --}}
            <nav class="flex flex-wrap gap-1 p-1 rounded-xl bg-gray-100/90 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80" aria-label="Availability sections">
                <a href="{{ route('availability.index', ['tab' => 'availability']) }}"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request('tab', 'availability') === 'availability' ? 'bg-white dark:bg-gray-900 text-velour-700 dark:text-velour-300 shadow-sm ring-1 ring-gray-200/80 dark:ring-gray-600' : 'text-muted hover:text-body hover:bg-white/60 dark:hover:bg-gray-900/40' }}">Availability</a>
                <a href="{{ route('availability.index', ['tab' => 'resources']) }}"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request('tab') === 'resources' ? 'bg-white dark:bg-gray-900 text-velour-700 dark:text-velour-300 shadow-sm ring-1 ring-gray-200/80 dark:ring-gray-600' : 'text-muted hover:text-body hover:bg-white/60 dark:hover:bg-gray-900/40' }}">Resources</a>
                <a href="{{ route('availability.index', ['tab' => 'leave']) }}"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request('tab') === 'leave' ? 'bg-white dark:bg-gray-900 text-velour-700 dark:text-velour-300 shadow-sm ring-1 ring-gray-200/80 dark:ring-gray-600' : 'text-muted hover:text-body hover:bg-white/60 dark:hover:bg-gray-900/40' }}">Leave</a>
                <a href="{{ route('availability.index', ['tab' => 'buffer']) }}"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors {{ request('tab') === 'buffer' ? 'bg-white dark:bg-gray-900 text-velour-700 dark:text-velour-300 shadow-sm ring-1 ring-gray-200/80 dark:ring-gray-600' : 'text-muted hover:text-body hover:bg-white/60 dark:hover:bg-gray-900/40' }}">Buffer Rules</a>
            </nav>
        </div>
    </div>

    {{-- Availability --}}
    @if($tab === 'availability')
        <div class="card p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h2 class="text-base sm:text-lg font-bold text-heading">Weekly availability</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('staff.index') }}" class="btn-outline text-sm inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit in Staff
                    </a>
                </div>
            </div>
            <p class="text-xs text-muted mb-3 leading-relaxed">Tap a day to toggle. No days set means all days (same as booking). <a href="{{ route('staff.index') }}" class="text-velour-600 hover:underline">Staff</a> for start/end times.</p>
            <div class="overflow-x-auto -mx-2 px-2">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left py-2 pr-3 font-semibold text-heading w-40">Staff</th>
                            @foreach($days as $d)
                                <th class="text-center py-2 px-1 font-semibold text-muted text-xs">{{ $d }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($staff as $s)
                            <tr>
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                              style="background-color: {{ $s->color ?: '#7c3aed' }}">{{ $staffInitials($s) }}</span>
                                        <span class="font-medium text-body truncate">{{ $s->name }}</span>
                                    </div>
                                </td>
                                @foreach($days as $d)
                                    @php $open = $staffDayOpen($s, $d); @endphp
                                    <td class="py-2 px-1 text-center">
                                        <form method="POST" action="{{ route('availability.staff.toggle-day', $s) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="day" value="{{ $d }}">
                                            <button type="submit"
                                                    class="w-9 h-9 rounded-lg mx-auto flex items-center justify-center transition-transform hover:scale-105
                                                           {{ $open ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-500' }}"
                                                    title="Toggle {{ $d }}">
                                                @if($open)
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-muted">No staff yet. <a href="{{ route('staff.create') }}" class="text-velour-600 font-medium hover:underline">Add staff</a> first.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Resources --}}
    @if($tab === 'resources')
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($resources as $r)
                @php
                    $typeEmoji = match ($r->type) {
                        'chair' => '✂️',
                        'station' => '🧩',
                        default => '🏠',
                    };
                @endphp
                <div class="card p-4 flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xl shrink-0" aria-hidden="true">{{ $typeEmoji }}</span>
                            <div class="min-w-0">
                                <p class="font-bold text-heading truncate">{{ $r->name }}</p>
                                <p class="text-xs text-muted capitalize">{{ $r->type }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full shrink-0
                            {{ $r->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' }}">
                            {{ $r->status }}
                        </span>
                    </div>
                    @if($r->equipment && count($r->equipment))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($r->equipment as $tag)
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-muted">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex items-center gap-2 mt-auto pt-2">
                        <div class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold text-center
                            {{ $r->availability_status === 'available' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200' : 'bg-amber-50 text-amber-900 dark:bg-amber-900/20 dark:text-amber-100' }}">
                            {{ $r->availability_status === 'available' ? 'Available' : 'In use' }}
                        </div>
                        <button type="button" @click="openResourceEdit({{ \Illuminate\Support\Js::from([
                            'id' => $r->id,
                            'name' => $r->name,
                            'type' => $r->type,
                            'capacity' => (int) $r->capacity,
                            'equipment' => $r->equipment ?? [],
                            'bookable' => (bool) $r->bookable,
                            'status' => $r->status,
                            'availability_status' => $r->availability_status,
                        ]) }})"
                                class="w-9 h-9 rounded-full border border-gray-200 dark:border-gray-600 flex items-center justify-center text-muted hover:text-velour-600 hover:border-velour-400 transition-colors shrink-0"
                                title="Edit">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach

            <button type="button" @click="openResourceAdd()"
                    class="card p-6 border-2 border-dashed border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center gap-2 min-h-[180px] hover:border-velour-400 hover:bg-velour-50/50 dark:hover:bg-velour-900/10 transition-colors text-muted hover:text-velour-600">
                <span class="text-3xl font-light">+</span>
                <span class="text-sm font-semibold">Add Resource</span>
            </button>
        </div>
    @endif

    {{-- Leave --}}
    @if($tab === 'leave')
        <div class="card p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h2 class="text-base sm:text-lg font-bold text-heading">Leave requests</h2>
                <button type="button" @click="openLeave()" class="btn-primary text-sm">+ New Request</button>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($leaveRequests as $req)
                    @php
                        $days = $req->start_date->diffInDays($req->end_date) + 1;
                    @endphp
                    <li class="py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-velour-100 dark:bg-velour-900/40 text-velour-700 dark:text-velour-300 flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $staffInitials($req->staff) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-heading">{{ $req->staff->name }}</p>
                                <p class="text-sm text-muted">
                                    {{ $req->leave_type }}
                                    · {{ $req->start_date->format('j M') }} → {{ $req->end_date->format('j M') }}
                                    · {{ $days }} {{ $days === 1 ? 'day' : 'days' }}
                                    @if($req->blocks_slots)
                                        <span class="text-amber-600 dark:text-amber-400">· blocks slots</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-full
                                {{ $req->status === 'approved' ? 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-200' : '' }}
                                {{ $req->status === 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : '' }}
                                {{ $req->status === 'rejected' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : '' }}">
                                {{ $req->status }}
                            </span>
                            @if($req->isPending())
                                <form method="POST" action="{{ route('availability.leave.approve', $req) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('availability.leave.reject', $req) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700 hover:bg-rose-200 dark:bg-rose-900/40 dark:text-rose-200">Reject</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-10 text-center text-muted text-sm">No leave requests yet.</li>
                @endforelse
            </ul>
        </div>
    @endif

    {{-- Buffer rules --}}
    @if($tab === 'buffer')
        <div class="card p-0 overflow-hidden">
            <form method="POST" action="{{ route('availability.buffer-rules.update') }}" class="max-w-2xl mx-auto">
                @csrf
                @method('PUT')
                <div class="px-4 sm:px-6 pt-5 sm:pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <h2 class="text-base sm:text-lg font-bold text-heading">Buffer time &amp; booking rules</h2>
                    <p class="text-xs text-muted mt-1">Values are saved per salon. Adjust numbers below, then save.</p>
                </div>
                @php
                    $rows = [
                        ['buffer_before_minutes', 'Buffer before service', 'Prep time before each appointment.', 'min'],
                        ['buffer_after_minutes', 'Buffer after service', 'Clean-up / turnaround time.', 'min'],
                        ['max_daily_bookings_per_staff', 'Max daily bookings per staff', 'Cap appointments per staff member per day.', 'appts'],
                        ['advance_booking_days', 'Advance booking window', 'How far ahead clients can book.', 'days'],
                        ['last_minute_cutoff_hours', 'Last-minute cut-off', 'Minimum notice before start time.', 'hours'],
                        ['overbooking_percent', 'Overbooking allowance', 'Extra capacity on busy days.', '%'],
                    ];
                @endphp
                <ul class="divide-y divide-gray-100 dark:divide-gray-800 px-4 sm:px-6">
                    @foreach($rows as [$field, $label, $help, $unit])
                        <li class="py-3.5 sm:py-3 grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_5.5rem_3rem] gap-2 sm:gap-x-4 sm:items-center">
                            <div class="min-w-0">
                                <label for="buf-{{ $field }}" class="font-medium text-body text-sm">{{ $label }}</label>
                                <p id="buf-help-{{ $field }}" class="text-xs text-muted mt-0.5 leading-snug">{{ $help }}</p>
                            </div>
                            <div class="flex items-center gap-2 sm:contents">
                                <input id="buf-{{ $field }}" type="number" name="{{ $field }}" value="{{ old($field, $bufferRule->$field) }}"
                                       aria-describedby="buf-help-{{ $field }}"
                                       class="form-input w-24 max-w-[40%] sm:max-w-none sm:w-[5.5rem] text-sm text-right tabular-nums py-2 px-2 sm:justify-self-end"
                                       min="0" max="{{ $field === 'overbooking_percent' ? 100 : ($field === 'advance_booking_days' ? 730 : 999) }}" required>
                                <span class="text-xs text-muted tabular-nums w-10 shrink-0 sm:w-auto">{{ $unit }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="px-4 sm:px-6 py-4 sm:py-5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800">
                    <button type="submit" class="btn-primary w-full sm:w-auto min-w-[10rem] text-sm">Save rules</button>
                </div>
            </form>
            <p class="text-xs text-muted px-4 sm:px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900/30 leading-relaxed max-w-2xl mx-auto">
                Booking today still uses each service’s own buffers and staff working days. Hooking these salon-wide rules into live availability can be added in a later release.
            </p>
        </div>
    @endif

    {{-- Modal: Leave --}}
    <div x-show="leaveOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="leaveOpen = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4" @click.outside="leaveOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-heading font-serif">Apply Leave / Block Time</h3>
                <button type="button" class="text-muted hover:text-heading" @click="closeLeave()">&times;</button>
            </div>
            <form method="POST" action="{{ route('availability.leave.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label text-xs uppercase tracking-wide">Staff member</label>
                    <select name="staff_id" required class="form-select text-sm">
                        <option value="">Select…</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs uppercase tracking-wide">Leave type</label>
                    <select name="leave_type" required class="form-select text-sm">
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Personal">Personal</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">From date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required class="form-input text-sm" value="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label text-xs">To date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required class="form-input text-sm" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div>
                    <label class="form-label text-xs">Notes</label>
                    <input type="text" name="notes" placeholder="Reason for leave (optional)" class="form-input text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm text-body cursor-pointer">
                    <input type="checkbox" name="blocks_slots" value="1" checked class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    Block all appointment slots during this period
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline text-sm" @click="closeLeave()">Cancel</button>
                    <button type="submit" class="btn-primary text-sm">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Resource --}}
    <div x-show="resourceOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="resourceOpen = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="closeResource()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-heading font-serif" x-text="resourceEditId ? 'Edit Resource' : 'Add Resource'"></h3>
                <button type="button" class="text-muted hover:text-heading" @click="closeResource()">&times;</button>
            </div>
            <form method="POST" x-bind:action="resourceAction()" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" disabled x-bind:disabled="!resourceEditId">
                <div>
                    <label class="form-label text-xs">Resource name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="resourceName" required class="form-input text-sm" placeholder="e.g. Suite C, Chair 3">
                </div>
                <div>
                    <label class="form-label text-xs">Type</label>
                    <select name="type" x-model="resourceType" class="form-select text-sm">
                        <option value="room">Room</option>
                        <option value="chair">Chair</option>
                        <option value="station">Station</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Capacity (people)</label>
                    <input type="number" name="capacity" x-model="resourceCapacity" min="1" max="99" class="form-input text-sm" required>
                </div>
                <div>
                    <label class="form-label text-xs">Equipment (comma separated)</label>
                    <input type="text" name="equipment" x-model="resourceEquipment" class="form-input text-sm" placeholder="Shampoo Bowl, Styling Chair, Mirror">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">Listing status</label>
                        <select name="status" x-model="resourceStatus" class="form-select text-sm">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Availability</label>
                        <select name="availability_status" x-model="resourceAvailability" class="form-select text-sm">
                            <option value="available">Available</option>
                            <option value="in_use">In use</option>
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-body cursor-pointer">
                    <input type="checkbox" name="bookable" value="1" x-model="resourceBookable" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    Available for booking from today
                </label>
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline text-sm" @click="closeResource()">Cancel</button>
                    <button type="submit" class="btn-primary text-sm" x-text="resourceEditId ? 'Save' : 'Add Resource'"></button>
                </div>
            </form>
            <form method="POST" class="pt-2 border-t border-gray-100 dark:border-gray-800" x-bind:action="'{{ $resourceBaseUrl }}/' + resourceEditId"
                  x-show="resourceEditId"
                  @submit="if (! confirm('Remove this resource?')) { $event.preventDefault() }">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-sm text-red-600 border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20">Delete resource</button>
            </form>
        </div>
    </div>
</div>
@endsection
