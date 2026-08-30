<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\StaffAttendanceRecord;

final class AdminTenantModuleRegistry
{
    /** @return array<string, array{label: string, icon: string, route: string, count_key: string}> */
    public static function hubModules(): array
    {
        return [
            'clients' => ['label' => 'Clients', 'icon' => 'clients', 'route' => 'clients.index', 'count_key' => 'clients'],
            'appointments' => ['label' => 'Appointments', 'icon' => 'appointments', 'route' => 'appointments.index', 'count_key' => 'appointments'],
            'staff' => ['label' => 'Staff', 'icon' => 'staff', 'route' => 'staff.index', 'count_key' => 'staff'],
            'pos' => ['label' => 'POS / Sales', 'icon' => 'pos', 'route' => 'pos.index', 'count_key' => 'pos_transactions'],
            'services' => ['label' => 'Services', 'icon' => 'services', 'route' => 'services.index', 'count_key' => 'services'],
            'inventory' => ['label' => 'Inventory', 'icon' => 'inventory', 'route' => 'inventory.index', 'count_key' => 'inventory'],
            'expenses' => ['label' => 'Expenses', 'icon' => 'expenses', 'route' => 'expenses.index', 'count_key' => 'expenses'],
            'reviews' => ['label' => 'Reviews', 'icon' => 'reviews', 'route' => 'reviews.index', 'count_key' => 'reviews'],
            'marketing' => ['label' => 'Marketing', 'icon' => 'marketing', 'route' => 'marketing.index', 'count_key' => 'marketing'],
            'leave' => ['label' => 'Leave', 'icon' => 'availability', 'route' => 'leave.index', 'count_key' => 'leave'],
            'attendance' => ['label' => 'Attendance', 'icon' => 'availability', 'route' => 'attendance.index', 'count_key' => 'attendance'],
            'settings' => ['label' => 'Settings', 'icon' => 'settings', 'route' => 'settings', 'count_key' => ''],
            'audit' => ['label' => 'Activity log', 'icon' => 'security', 'route' => 'audit.index', 'count_key' => ''],
            'deleted' => ['label' => 'Deleted items', 'icon' => 'trash', 'route' => 'deleted.index', 'count_key' => ''],
        ];
    }

    /** @return class-string<\Illuminate\Database\Eloquent\Model>|null */
    public static function modelFor(string $module): ?string
    {
        return match ($module) {
            'clients' => Client::class,
            'appointments' => Appointment::class,
            'staff' => Staff::class,
            'pos' => PosTransaction::class,
            'services' => Service::class,
            'inventory' => InventoryItem::class,
            'expenses' => Expense::class,
            'reviews' => Review::class,
            'marketing' => MarketingCampaign::class,
            'leave' => StaffLeaveRequest::class,
            'attendance' => StaffAttendanceRecord::class,
            default => null,
        };
    }

    public static function label(string $module): string
    {
        return self::hubModules()[$module]['label'] ?? ucfirst($module);
    }
}
