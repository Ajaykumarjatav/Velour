@extends('layouts.app')
@section('title', 'Guide')
@section('page-title', 'How to use Velour')
@section('content')

<div class="max-w-3xl space-y-6">
    <div class="card p-6 space-y-3">
        <h2 class="section-title">Welcome to {{ $salon->name }}</h2>
        <p class="text-sm text-muted">
            This guide helps a new tenant configure and use every core feature of the system. Use it as your checklist when you go live or when training a new team member.
        </p>
        @php
            $isStylistScoped = auth()->user()->dashboardScopedStaffId() !== null;
        @endphp
        @if($isStylistScoped)
            <p class="text-xs text-amber-600 dark:text-amber-400">
                You are signed in to a <strong>staff / stylist</strong> view. Some setup menus are hidden; focus on Calendar, Appointments, Clients and POS sections below.
            </p>
        @else
            <p class="text-xs text-emerald-700 dark:text-emerald-400">
                You are viewing the <strong>owner / admin</strong> guide. Complete the quick checklist below before inviting your team.
            </p>
        @endif
    </div>

    <div class="card p-6 space-y-4">
        <h3 class="section-title">Quick setup checklist (admin)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('settings.index', ['tab' => 'salon']) }}" class="sidebar-link !no-underline">
                <span class="font-semibold">1. Salon profile</span>
                <span class="text-muted text-xs block mt-1">Currency, timezone, and branding basics</span>
            </a>
            <a href="{{ route('settings.index', ['tab' => 'hours']) }}" class="sidebar-link !no-underline">
                <span class="font-semibold">2. Opening hours</span>
                <span class="text-muted text-xs block mt-1">Salon schedule rules</span>
            </a>
            <a href="{{ route('staff.index') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">3. Staff &amp; roles</span>
                <span class="text-muted text-xs block mt-1">Create your team and job roles</span>
            </a>
            <a href="{{ route('availability.index') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">4. Working days &amp; leave</span>
                <span class="text-muted text-xs block mt-1">Availability engine inputs</span>
            </a>
            <a href="{{ route('service-categories.index') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">5. Services catalog</span>
                <span class="text-muted text-xs block mt-1">Categories, services, variants</span>
            </a>
            <a href="{{ route('clients.index') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">6. Add clients</span>
                <span class="text-muted text-xs block mt-1">Clients list + quick import</span>
            </a>
            <a href="{{ route('appointments.create') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">7. Book a test appointment</span>
                <span class="text-muted text-xs block mt-1">Confirm staff/service availability</span>
            </a>
            <a href="{{ route('pos.index') }}" class="sidebar-link !no-underline">
                <span class="font-semibold">8. POS checkout</span>
                <span class="text-muted text-xs block mt-1">Sell services/products and create vouchers</span>
            </a>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h3 class="section-title">Visual walkthrough (screenshots)</h3>
            <span class="text-xs text-muted">Click a screenshot to open the related page</span>
        </div>

        @if(!empty($screenshots))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($screenshots as $shot)
                    <a href="{{ $shot['link'] }}" class="group rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900/40 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                        <img src="{{ $shot['url'] }}" alt="{{ $shot['title'] }} screenshot" class="w-full h-40 object-cover">
                        <div class="p-3">
                            <p class="font-semibold text-sm text-gray-900 dark:text-gray-100 group-hover:text-velour-700 dark:group-hover:text-velour-300">{{ $shot['title'] }}</p>
                            <p class="text-xs text-muted mt-1">{{ $shot['caption'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-900/30">
                <p class="text-sm text-muted">
                    No screenshots added yet. Add guide images to <code>storage/app/public/guide/</code> using these names:
                    <code>dashboard-overview.png</code>,
                    <code>appointments-create.png</code>,
                    <code>calendar-view.png</code>,
                    <code>pos-checkout.png</code>,
                    <code>marketing-campaign.png</code>.
                </p>
            </div>
        @endif
    </div>

    <div class="card p-6 space-y-4">
        <h3 class="section-title">Daily routine (staff &amp; admin)</h3>
        <ol class="list-decimal pl-5 text-sm text-muted space-y-1.5">
            <li>
                <span class="font-semibold text-body">Start of day:</span>
                Open <a href="{{ route('calendar') }}" class="text-link">Calendar</a> and check today’s appointments. Confirm new bookings from the
                <a href="{{ route('appointments.index') }}" class="text-link">Appointments</a> list if needed.
            </li>
            <li>
                <span class="font-semibold text-body">During the day:</span>
                Use Calendar &amp; Appointments to track clients as they arrive, and <a href="{{ route('pos.index') }}" class="text-link">POS</a> to record payments.
            </li>
            <li>
                <span class="font-semibold text-body">End of day:</span>
                Review <a href="{{ route('revenue.index') }}" class="text-link">Revenue</a>, reply to new
                <a href="{{ route('reviews.index') }}" class="text-link">Reviews</a>, and check <a href="{{ route('notifications.index') }}" class="text-link">Notifications</a>.
            </li>
        </ol>
    </div>

    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h3 class="section-title">Feature reference</h3>
            <p class="text-xs text-muted">Where to go and what to do</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="{{ route('dashboard') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Dashboard</p>
                <p class="text-sm text-muted mt-1">Daily business snapshot (revenue, bookings, alerts).</p>
                <p class="text-xs text-muted mt-2">Tip: KPI cards are your fastest way to jump to action screens.</p>
            </a>

            <a href="{{ route('calendar') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Calendar</p>
                <p class="text-sm text-muted mt-1">Visual schedule for each staff member.</p>
                <p class="text-xs text-muted mt-2">Tip: use staff filter for focused view.</p>
            </a>

            <a href="{{ route('appointments.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Appointments</p>
                <p class="text-sm text-muted mt-1">Create, confirm, reschedule, complete bookings.</p>
                <p class="text-xs text-muted mt-2">Tip: select staff first to auto-filter services.</p>
            </a>

            <a href="{{ route('clients.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Clients</p>
                <p class="text-sm text-muted mt-1">Profiles, consent, loyalty, visit history.</p>
                <p class="text-xs text-muted mt-2">Tip: marketing consent controls campaign audience.</p>
            </a>

            <a href="{{ route('staff.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Staff &amp; Services eligibility</p>
                <p class="text-sm text-muted mt-1">Assign roles, permissions, and service capabilities.</p>
                <p class="text-xs text-muted mt-2">Tip: missing services on booking usually means mapping is incomplete.</p>
            </a>

            <a href="{{ route('services.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Services</p>
                <p class="text-sm text-muted mt-1">Service menu, pricing, variants and add-ons.</p>
                <p class="text-xs text-muted mt-2">Tip: keep service names short and descriptions detailed.</p>
            </a>

            <a href="{{ route('availability.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Availability &amp; Resources</p>
                <p class="text-sm text-muted mt-1">Working days, leave, and buffer settings.</p>
                <p class="text-xs text-muted mt-2">Tip: approved leave blocks booking slots automatically.</p>
            </a>

            <a href="{{ route('pos.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Point of Sale (POS)</p>
                <p class="text-sm text-muted mt-1">Checkout for services and retail products.</p>
                <p class="text-xs text-muted mt-2">Tip: assign client at checkout to keep records accurate.</p>
            </a>

            <a href="{{ route('inventory.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Inventory &amp; Retail</p>
                <p class="text-sm text-muted mt-1">Stock levels, suppliers, reorder and adjustments.</p>
                <p class="text-xs text-muted mt-2">Tip: low-stock thresholds improve purchasing decisions.</p>
            </a>

            <a href="{{ route('marketing.growth') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Marketing</p>
                <p class="text-sm text-muted mt-1">Email/SMS campaigns with audience segments.</p>
                <p class="text-xs text-muted mt-2">Tip: segment count updates live before sending.</p>
            </a>

            <a href="{{ route('reports.index') }}" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Reports, Reviews, Settings</p>
                <p class="text-sm text-muted mt-1">Track growth, handle feedback, and tune configuration.</p>
                <p class="text-xs text-muted mt-2">Tip: if behaviour feels wrong, start with Settings → Business/Hours.</p>
            </a>
        </div>
    </div>

    <div class="card p-6 space-y-3">
        <h3 class="section-title">Need help?</h3>
        <p class="text-sm text-muted">
            If something doesn’t match what you expect, start with:
        </p>
        <ul class="list-disc pl-5 text-sm text-muted space-y-1">
            <li><a href="{{ route('setup-progress') }}" class="text-link">Setup progress</a> for guided onboarding</li>
            <li><a href="{{ route('availability.index') }}" class="text-link">Availability</a> if appointment slots are blocked</li>
            <li><a href="{{ route('staff.index') }}" class="text-link">Staff &amp; roles</a> if services don’t appear in appointment booking</li>
        </ul>
    </div>
</div>

@endsection

