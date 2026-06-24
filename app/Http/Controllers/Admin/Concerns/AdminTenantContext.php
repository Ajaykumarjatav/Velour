<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Salon;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait AdminTenantContext
{
    protected function resolveSalon(int|string $salonId): Salon
    {
        return Salon::withoutGlobalScopes()->findOrFail($salonId);
    }

    protected function authorizeSalonRecord(Salon $salon, Model $record): void
    {
        abort_unless(
            isset($record->salon_id) && (int) $record->salon_id === (int) $salon->id,
            404
        );
    }

    protected function logTenantView(Salon $salon, string $module, ?Model $record = null, array $meta = []): void
    {
        if (! app()->bound(AuditLogService::class)) {
            return;
        }

        $desc = $record
            ? "Admin viewed {$module} #{$record->getKey()} for salon '{$salon->name}'"
            : "Admin viewed {$module} list for salon '{$salon->name}'";

        app(AuditLogService::class)->admin(
            'admin.tenant.view',
            $desc,
            $record ?? $salon,
            array_merge(['module' => $module, 'salon_id' => $salon->id], $meta)
        );
    }
}
