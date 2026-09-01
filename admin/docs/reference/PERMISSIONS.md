# Roles and permissions

Source: `database/seeders/RolesAndPermissionsSeeder.php`

Run: `php artisan db:seed --class=RolesAndPermissionsSeeder`

## Roles

| Role | Scope | Description |
|------|-------|-------------|
| `super_admin` | Platform | All permissions |
| `tenant_admin` | Salon | Owner — full salon access incl. billing & users |
| `manager` | Salon | Operations — no billing/user delete |
| `stylist` | Salon | Own appointments/clients; scoped dashboard |
| `receptionist` | Salon | Front desk — calendar, clients, POS |

## Permission list

### Appointments
- `appointments.view`
- `appointments.view-all`
- `appointments.create`
- `appointments.edit`
- `appointments.delete`
- `appointments.update-status`

### Clients
- `clients.view`, `clients.create`, `clients.edit`, `clients.delete`
- `clients.view-notes`, `clients.manage-notes`

### Staff
- `staff.view`, `staff.create`, `staff.edit`, `staff.delete`

### Services
- `services.view`, `services.create`, `services.edit`, `services.delete`

### Inventory
- `inventory.view`, `inventory.create`, `inventory.edit`, `inventory.delete`, `inventory.adjust-stock`

### Facilities
- `facilities.view`, `facilities.manage`

### POS
- `pos.view`, `pos.create`, `pos.refund`

### Marketing
- `marketing.view`, `marketing.create`, `marketing.edit`, `marketing.delete`, `marketing.send`

### Reports
- `reports.view`, `reports.export`

### Reviews
- `reviews.view`, `reviews.reply`, `reviews.delete`

### Settings
- `settings.view`, `settings.edit`

### Users (salon team)
- `users.view`, `users.invite`, `users.edit`, `users.delete`

### Billing
- `billing.view`, `billing.manage`

### Gates (not Spatie permissions)

Defined in `AppServiceProvider`:

| Gate | Who |
|------|-----|
| `view-activity-log` | Super admin, `tenant_admin`, or any user linked to a salon |
| `view-audit-logs` | Super admin only |
| `impersonate-users` | Super admin only |

## Usage in code

```php
$user->can('reports.view');
$user->hasAnyRole(['tenant_admin', 'manager']);
Gate::authorize('update', $appointment);
```

## Sidebar mapping

`App\Support\SidebarNav::show($user, $item)` maps nav keys to permissions/roles. See [../02-architecture.md](../02-architecture.md).

## Subscription features (separate from RBAC)

Middleware examples:

- `subscription:feature:reports`
- `subscription:feature:marketing`
- `subscription:feature:multi_location`
- `plan.limit:staff`, `plan.limit:services`

Controlled when `SUBSCRIPTIONS_ENABLED=true` in `.env`.
