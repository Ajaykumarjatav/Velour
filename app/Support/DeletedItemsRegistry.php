<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class DeletedItemsRegistry
{
    /**
     * @return array<string, array{
     *   model: class-string<Model>,
     *   label: string,
     *   plural: string,
     *   view_permission: string,
     *   restore_permission: string,
     *   name: callable,
     * }>
     */
    public static function types(): array
    {
        return [
            'client' => [
                'model' => Client::class,
                'label' => 'Client',
                'plural' => 'Clients',
                'view_permission' => 'clients.view',
                'restore_permission' => 'clients.delete',
                'name' => static fn (Model $m): string => trim(
                    ($m->first_name ?? '').' '.($m->last_name ?? '')
                ) ?: 'Client #'.$m->getKey(),
            ],
            'staff' => [
                'model' => Staff::class,
                'label' => 'Staff member',
                'plural' => 'Staff',
                'view_permission' => 'staff.view',
                'restore_permission' => 'staff.delete',
                'name' => static fn (Model $m): string => (string) ($m->name ?: 'Staff #'.$m->getKey()),
            ],
            'service' => [
                'model' => Service::class,
                'label' => 'Service',
                'plural' => 'Services',
                'view_permission' => 'services.view',
                'restore_permission' => 'services.delete',
                'name' => static fn (Model $m): string => (string) ($m->name ?: 'Service #'.$m->getKey()),
            ],
            'service_package' => [
                'model' => ServicePackage::class,
                'label' => 'Plan / package',
                'plural' => 'Plans / packages',
                'view_permission' => 'services.view',
                'restore_permission' => 'services.delete',
                'name' => static fn (Model $m): string => (string) ($m->name ?: 'Package #'.$m->getKey()),
            ],
            'inventory_item' => [
                'model' => InventoryItem::class,
                'label' => 'Product',
                'plural' => 'Inventory',
                'view_permission' => 'inventory.view',
                'restore_permission' => 'inventory.delete',
                'name' => static fn (Model $m): string => (string) ($m->name ?: 'Product #'.$m->getKey()),
            ],
            'marketing_campaign' => [
                'model' => MarketingCampaign::class,
                'label' => 'Campaign',
                'plural' => 'Marketing',
                'view_permission' => 'marketing.view',
                'restore_permission' => 'marketing.delete',
                'name' => static fn (Model $m): string => (string) ($m->name ?: 'Campaign #'.$m->getKey()),
            ],
            'appointment' => [
                'model' => Appointment::class,
                'label' => 'Appointment',
                'plural' => 'Appointments',
                'view_permission' => 'appointments.view',
                'restore_permission' => 'appointments.delete',
                'name' => static function (Model $m): string {
                    $ref = $m->reference ?? null;

                    return $ref ? 'Appointment '.$ref : 'Appointment #'.$m->getKey();
                },
            ],
        ];
    }

    public static function type(string $key): ?array
    {
        return self::types()[$key] ?? null;
    }

    public static function userCanAccessTrash(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon() || $user->hasRole('tenant_admin')) {
            return true;
        }

        foreach (self::types() as $config) {
            if ($user->can($config['view_permission']) || $user->can($config['restore_permission'])) {
                return true;
            }
        }

        return false;
    }

    public static function userCanViewType(User $user, string $typeKey): bool
    {
        $config = self::type($typeKey);
        if (! $config) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->ownsCurrentSalon() || $user->hasRole('tenant_admin')) {
            return true;
        }

        return $user->can($config['view_permission']);
    }

    public static function userCanRestoreType(User $user, string $typeKey): bool
    {
        $config = self::type($typeKey);
        if (! $config) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->ownsCurrentSalon() || $user->hasRole('tenant_admin')) {
            return true;
        }

        return $user->can($config['restore_permission']);
    }

    public static function userCanForceDelete(User $user): bool
    {
        return $user->isSuperAdmin() || $user->ownsCurrentSalon() || $user->hasRole('tenant_admin');
    }

    /**
     * @return Collection<int, array{
     *   type: string,
     *   type_label: string,
     *   id: int,
     *   name: string,
     *   deleted_at: string,
     *   can_restore: bool,
     *   can_force_delete: bool,
     * }>
     */
    public static function itemsForSalon(int $salonId, User $user): Collection
    {
        $rows = collect();

        foreach (self::types() as $typeKey => $config) {
            if (! self::userCanViewType($user, $typeKey)) {
                continue;
            }

            /** @var class-string<Model> $modelClass */
            $modelClass = $config['model'];

            $models = $modelClass::withoutGlobalScopes()
                ->onlyTrashed()
                ->where('salon_id', $salonId)
                ->orderByDesc('deleted_at')
                ->limit(100)
                ->get();

            foreach ($models as $model) {
                $rows->push([
                    'type' => $typeKey,
                    'type_label' => $config['label'],
                    'id' => (int) $model->getKey(),
                    'name' => $config['name']($model),
                    'deleted_at' => $model->deleted_at?->diffForHumans() ?? '—',
                    'deleted_at_raw' => $model->deleted_at?->toDateTimeString() ?? '',
                    'can_restore' => self::userCanRestoreType($user, $typeKey),
                    'can_force_delete' => self::userCanForceDelete($user),
                ]);
            }
        }

        return $rows->sortByDesc('deleted_at_raw')->values();
    }

    public static function countForSalon(int $salonId, User $user): int
    {
        $total = 0;

        foreach (self::types() as $typeKey => $config) {
            if (! self::userCanViewType($user, $typeKey)) {
                continue;
            }

            /** @var class-string<Model> $modelClass */
            $modelClass = $config['model'];
            $total += $modelClass::withoutGlobalScopes()
                ->onlyTrashed()
                ->where('salon_id', $salonId)
                ->count();
        }

        return $total;
    }
}
