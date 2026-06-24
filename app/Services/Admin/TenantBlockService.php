<?php

namespace App\Services\Admin;

use App\Models\Salon;
use App\Models\User;
use App\Notifications\Admin\TenantSuspendedNotification;
use App\Notifications\Admin\TenantUnsuspendedNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantBlockService
{
    public function __construct(protected AuditLogService $audit) {}

    public function isBlocked(User $user): bool
    {
        return ! $user->is_active;
    }

    /**
     * Block tenant owner: disables login and suspends all stores.
     */
    public function block(User $owner, string $reason, ?string $notes = null, ?string $customerMessage = null, bool $notify = true): void
    {
        if ($owner->id === Auth::id()) {
            throw new \InvalidArgumentException('You cannot block your own account.');
        }

        if ($owner->isSuperAdmin()) {
            throw new \InvalidArgumentException('Super admin accounts cannot be blocked from here.');
        }

        DB::transaction(function () use ($owner, $reason, $notes, $customerMessage) {
            $owner->update(['is_active' => false]);
            $owner->tokens()->delete();

            Salon::withoutGlobalScopes()
                ->where('owner_id', $owner->id)
                ->where('is_active', true)
                ->each(function (Salon $salon) use ($reason, $notes, $customerMessage) {
                    $salon->update([
                        'is_active'         => false,
                        'suspension_reason' => $reason,
                        'suspended_at'      => now(),
                        'suspended_by'      => Auth::id(),
                    ]);

                    DB::table('salon_suspensions')->insert([
                        'salon_id'         => $salon->id,
                        'suspended_by'     => Auth::id(),
                        'reason'           => $reason,
                        'notes'            => $notes,
                        'customer_message' => $customerMessage,
                        'suspended_at'     => now(),
                    ]);
                });
        });

        if ($notify) {
            $primary = Salon::withoutGlobalScopes()->where('owner_id', $owner->id)->first();
            if ($primary) {
                $this->notifySafely($owner, new TenantSuspendedNotification($primary, $reason, $customerMessage));
            }
        }

        $this->audit->admin(
            'admin.tenant.block',
            "Blocked tenant account {$owner->email} (#{$owner->id}) — reason: {$reason}",
            $owner,
            ['reason' => $reason]
        );
    }

    /**
     * Unblock tenant owner: restores login and reactivates all stores.
     */
    public function unblock(User $owner, ?string $reason = null, ?string $customerMessage = null, bool $notify = true): void
    {
        DB::transaction(function () use ($owner, $reason) {
            $owner->update(['is_active' => true]);

            Salon::withoutGlobalScopes()
                ->where('owner_id', $owner->id)
                ->where('is_active', false)
                ->each(function (Salon $salon) use ($reason) {
                    $salon->update([
                        'is_active'         => true,
                        'suspension_reason' => null,
                        'suspended_at'      => null,
                        'suspended_by'      => null,
                    ]);

                    DB::table('salon_suspensions')
                        ->where('salon_id', $salon->id)
                        ->whereNull('unsuspended_at')
                        ->update([
                            'unsuspended_at'   => now(),
                            'unsuspended_by'   => Auth::id(),
                            'unsuspend_reason' => $reason,
                        ]);
                });
        });

        if ($notify) {
            $primary = Salon::withoutGlobalScopes()->where('owner_id', $owner->id)->first();
            if ($primary) {
                $this->notifySafely($owner, new TenantUnsuspendedNotification($primary, $customerMessage));
            }
        }

        $this->audit->admin(
            'admin.tenant.unblock',
            "Unblocked tenant account {$owner->email} (#{$owner->id})",
            $owner,
            ['reason' => $reason]
        );
    }

    private function notifySafely(User $owner, object $notification): void
    {
        try {
            $owner->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('[TenantBlock] Owner notification failed (block/unblock still applied)', [
                'owner_id' => $owner->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
