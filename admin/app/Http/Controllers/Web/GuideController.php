<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Support\SalonSetupProgress;
use App\Support\SalonUrl;
use App\Support\SidebarNav;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GuideController extends Controller
{
    use ResolvesActiveSalon;

    public function index(): View
    {
        $user = Auth::user();
        $salon = $this->activeSalon();
        $store = SalonUrl::key($salon);
        $progress = SalonSetupProgress::forSalon($salon);
        $isStylistScoped = $user->dashboardScopedStaffId() !== null;
        $show = fn (string $key): bool => SidebarNav::show($user, $key);

        $featureGroups = [];

        $main = array_values(array_filter([
            $show('dashboard') ? ['title' => 'Dashboard', 'hint' => 'Today’s revenue, bookings, and alerts.', 'href' => SalonUrl::dashboardUrl($user)] : null,
            $show('tasks') ? ['title' => 'Tasks', 'hint' => 'Follow-ups and to-dos for the salon.', 'href' => route('tasks.index')] : null,
            $show('calendar') ? ['title' => 'Calendar', 'hint' => 'Staff schedule. Filter by team member if needed.', 'href' => route('calendar')] : null,
            $show('appointments') ? ['title' => 'Appointments', 'hint' => 'Create, confirm, reschedule, or complete. Any bookable staff can take any service.', 'href' => route('appointments.index')] : null,
            $show('clients') ? ['title' => 'Clients', 'hint' => 'Profiles, visit history, loyalty, and marketing consent.', 'href' => route('clients.index')] : null,
        ]));
        if ($main !== []) {
            $featureGroups[] = ['label' => 'Daily work', 'items' => $main];
        }

        $business = array_values(array_filter([
            $show('staff') ? ['title' => 'Staff & HR', 'hint' => 'Team profiles. Settings → Team also adds bookable staff for online booking.', 'href' => route('staff.index')] : null,
            $show('services') ? ['title' => 'Services', 'hint' => 'Menu, prices, variants. Turn on online booking on services clients can book.', 'href' => route('services.index')] : null,
            $show('service_packages') ? ['title' => 'Plans / Packages', 'hint' => 'Bundles clients can buy or book.', 'href' => route('service-packages.index')] : null,
            $show('multi_location') ? ['title' => 'Multi-Location', 'hint' => 'Branches, switch location, consolidated view.', 'href' => route('multi-location.index')] : null,
            $show('availability') ? ['title' => 'Availability & Resources', 'hint' => 'Working days, leave, and rooms. Leave blocks slots automatically.', 'href' => route('availability.index')] : null,
            $show('inventory') ? ['title' => 'Inventory & Retail', 'hint' => 'Stock, suppliers, and retail products for POS.', 'href' => route('inventory.index')] : null,
            $show('expenses') ? ['title' => 'Expenses', 'hint' => 'Salon costs and categories.', 'href' => route('expenses.index')] : null,
            $show('pos') ? ['title' => 'Point of Sale', 'hint' => 'Checkout for services and products. Attach a client when you can.', 'href' => route('pos.index')] : null,
        ]));
        if ($business !== []) {
            $featureGroups[] = ['label' => 'Business', 'items' => $business];
        }

        $website = array_values(array_filter([
            $show('go_live') ? ['title' => 'Go Live & Share', 'hint' => 'Booking website, theme, photos, and share link.', 'href' => route('go-live')] : null,
            $show('website_about') ? ['title' => 'About Us', 'hint' => 'Who we are heading, story, and stats on the booking site.', 'href' => route('website-about.index')] : null,
            $show('website_seo') ? ['title' => 'Website & SEO', 'hint' => 'Theme, booking widget, and SEO.', 'href' => route('website-seo.index')] : null,
            $show('customization') ? ['title' => 'Customization', 'hint' => 'Brand identity, colours, and white-label options.', 'href' => route('customization.index')] : null,
        ]));
        if ($website !== []) {
            $featureGroups[] = ['label' => 'Website', 'items' => $website];
        }

        $growth = array_values(array_filter([
            $show('marketing') ? ['title' => 'Marketing', 'hint' => 'Email / SMS campaigns. Audience follows client consent.', 'href' => route('marketing.growth')] : null,
            $show('reviews') ? ['title' => 'Reviews', 'hint' => 'Client feedback after visits.', 'href' => route('reviews.index')] : null,
            $show('analytics') ? ['title' => 'Analytics', 'hint' => 'Trends across bookings and revenue.', 'href' => route('reports.analytics')] : null,
            $show('reports_menu') ? ['title' => 'Reports', 'hint' => 'Revenue and other reports under Growth.', 'href' => route('reports.index')] : null,
            $show('growth_tips') ? ['title' => 'Growth Tips', 'hint' => 'Ideas to fill the calendar.', 'href' => route('reports.growth-tips')] : null,
        ]));
        if ($growth !== []) {
            $featureGroups[] = ['label' => 'Growth', 'items' => $growth];
        }

        $account = array_values(array_filter([
            $show('settings') ? ['title' => 'Settings', 'hint' => 'Business, booking, hours, team, notifications, profile.', 'href' => route('settings.index')] : null,
            $show('security_support') ? ['title' => 'Security & 2FA', 'hint' => 'Password and two-factor login.', 'href' => route('security-support.index')] : null,
            $show('support') ? ['title' => 'Support tickets', 'hint' => 'Report a store issue. Super admin sees it and both of you get email updates.', 'href' => route('support-tickets.index')] : null,
            $show('notifications') ? ['title' => 'Notifications', 'hint' => 'Salon alerts for bookings and payments.', 'href' => route('notifications.index')] : null,
            config('billing.subscriptions_enabled') && $show('billing')
                ? ['title' => 'Billing', 'hint' => 'Plan, invoices, and Cashfree checkout.', 'href' => route('billing.dashboard')]
                : null,
            SidebarNav::showAccountTeam($user)
                ? ['title' => 'Admin → Team', 'hint' => 'Invite staff to log in after their Staff & HR profile exists.', 'href' => route('salon-admin.team')]
                : null,
            config('billing.subscriptions_enabled') && $show('billing') && ($user->ownsCurrentSalon() || $user->hasRole('tenant_admin') || $user->isSuperAdmin())
                ? ['title' => 'Admin → Subscription', 'hint' => 'Current plan, usage, upgrade or cancel.', 'href' => route('salon-admin.subscription')]
                : null,
        ]));
        if ($account !== []) {
            $featureGroups[] = ['label' => 'Account', 'items' => $account];
        }

        $screenDefs = [
            [
                'path' => 'guide/dashboard-overview.png',
                'title' => 'Dashboard',
                'caption' => 'KPIs and shortcuts for today.',
                'link' => SalonUrl::dashboardUrl($user),
            ],
            [
                'path' => 'guide/calendar-view.png',
                'title' => 'Calendar',
                'caption' => 'See the day’s bookings by staff.',
                'link' => route('calendar', ['store' => $store]),
            ],
            [
                'path' => 'guide/appointments-create.png',
                'title' => 'Appointments',
                'caption' => 'Client, staff, services, and time slot.',
                'link' => route('appointments.index', ['store' => $store]),
            ],
            [
                'path' => 'guide/pos-checkout.png',
                'title' => 'POS',
                'caption' => 'Take payment for services and products.',
                'link' => route('pos.index', ['store' => $store]),
            ],
            [
                'path' => 'guide/go-live.png',
                'title' => 'Go Live & Share',
                'caption' => 'Theme, photos, and public booking link.',
                'link' => route('go-live', ['store' => $store]),
            ],
            [
                'path' => 'guide/marketing-campaign.png',
                'title' => 'Marketing',
                'caption' => 'Campaigns to clients who opted in.',
                'link' => route('marketing.growth', ['store' => $store]),
            ],
        ];

        $screenshots = collect($screenDefs)
            ->filter(fn (array $item) => Storage::disk('public')->exists($item['path']))
            ->map(fn (array $item) => [
                ...$item,
                'url' => asset('storage/'.$item['path']),
            ])
            ->values()
            ->all();

        return view('guide.index', [
            'salon' => $salon,
            'screenshots' => $screenshots,
            'progress' => $progress,
            'isStylistScoped' => $isStylistScoped,
            'featureGroups' => $featureGroups,
            'showGoLive' => $show('go_live'),
            'showBilling' => config('billing.subscriptions_enabled') && $show('billing'),
            'showAvailability' => $show('availability'),
            'showStaff' => $show('staff'),
        ]);
    }
}
